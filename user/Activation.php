<?php
session_start();
include_once '../include/classes/DatabaseServiceClass.php';
include_once '../include/classes/SanitizeClass.php';
include_once '../include/classes/ExistsClass.php';

@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");




$response = array('message' => null);
$response_code = 400;



// if($_SERVER['REQUEST_METHOD'] == "POST" && count($_POST) == 0){

//     // Generate code , then send it 


//     if(Exists::isUserActivation($_SESSION['id']))
//         return header('HTTP/1.1 400 bad request');

        

//     $token = null;
//     do{
//         $token = bin2hex(random_bytes(16));
//     }while(Exists::activationExists($token));

    
//     $DatabaseService = new DatabaseService;

//     $connect = $DatabaseService->getConnection();


//     try{
//         $stmt = $connect->prepare("UPDATE users SET activation_token = ? , user_activation_at = ? WHERE user_id = ?");
//         $date = date('Y-m-d H:i:s');

        

//         $stmt->execute(array($token , $date , $_SESSION['id']));


//         if($stmt->rowCount()){
//             $response['message'] = 'Activation token has been created';
//             $response_code = 200;
//         }

//     }catch(PDOException $e){

//         $response['message'] = 'Error while generating token';
//         $response_code = 400;
//     }


//     echo json_encode($response);
//     http_response_code($response_code);


// }



$_POST = !empty(file_get_contents('php://input', true)) ? Sanitize::jsonValidator(file_get_contents("php://input")) : [];


if($_SERVER['REQUEST_METHOD'] == "POST" && count($_POST) == 1 && !empty($_POST['token']) && strlen($_POST['token']) == 32){



    // Activate code using get request

    
    $DatabaseService = new DatabaseService;
    $connect = $DatabaseService->getConnection();

    
    $token = $_POST['token'];



    try{
        $stmt = $connect->prepare("SELECT user_id, user_activation_at, user_activation ,activation_request FROM users WHERE activation_token = ? LIMIT 1");
        $stmt->execute(array($token));
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    }catch(PDOException $e){

        header('HTTP/1.1 400 bad request');
    }

    


    
    $expiration = !empty($user['user_activation_at'])  ? (strtotime(date('Y-m-d H:i:s')) - strtotime($user['user_activation_at']))/60 : 0;


    $user_id = !empty($user['user_id']) ? $user['user_id'] : 0;
    $requests = isset($user['activation_request']) ? $user['activation_request'] : 20;
    $activation = isset($user['user_activation']) ? $user['user_activation'] : 0;


    if($expiration < 60 && $user_id && $requests < 10){

        if(!$activation){

            try{
                $stmt = $connect->prepare("UPDATE users SET user_activation = 1 WHERE user_id = ?");
                $stmt->execute(array($user_id));

                if($stmt->rowCount()){
                    $response['message'] = 'Your account has been verified successfully';
                    $response_code = 200;
                }
            }catch(PDOException $e){
                $response['message'] = 'Unknow error while verifying';
            }

        }else{
            $response['message'] = "Your account already has been verified";
        }
    }else{
        $response['message'] = 'Your token has been expired';
    }



}else{
    
    $response['message'] = "Your token doesn't match our records";

}

echo json_encode($response);
    

http_response_code($response_code);
?>