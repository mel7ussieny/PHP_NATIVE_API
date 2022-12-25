<?php
    session_start();
    include_once '../include/classes/DatabaseServiceClass.php';
    include_once '../include/classes/SanitizeClass.php';
    include_once '../include/classes/AuthClass.php';
    include_once '../include/classes/ExistsClass.php';
    
    



    @header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");
    
    Auth::isAuth();


    $response = array("message" => "");
    $response_code = 200;


    $data = Sanitize::jsonValidator(file_get_contents("php://input"));

    $arr_param = ['first_name', 'last_name', 'email_address' ,'country','created_at'];

    $validate = false;

    if($_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 5){
        $validate = true;
        foreach($arr_param as $key => $value){
            if(empty($data[$value])){
                $response = array("message" => "Missing field ". $value);
                $response_code = 400;
                $validate = false;
                break;
            }
        }
    
    }
    

    if($validate){
        $DatabaseService = new DatabaseService;
        $connect = $DatabaseService->getConnection();

        try{
            $stmt = $connect->prepare("UPDATE users SET first_name = ?, last_name = ? , country = ? WHERE user_id = ?");
            $stmt->execute(array($data['first_name'], $data['last_name'], $data['country'], $_SESSION['id']));
            $response['message'] = "User has been updated successfully";
        }catch(PDOException $e){
            $response['message'] = "Server error!";
            $response_code = 400;
        }
        
    }

    echo json_encode($response);
    http_response_code($response_code);
    
?>