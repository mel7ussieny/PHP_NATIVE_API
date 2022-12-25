<?php
    session_start();
    include_once '../include/classes/DatabaseServiceClass.php';
    include_once '../include/classes/SanitizeClass.php';
    include_once '../include/classes/AuthClass.php';
    
    



    @header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");
    
    Auth::isAuth();
    
    $response = array("message" => 'Something went wrong please try again', "name" => null);
    $response_code = 400;



    if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES['picture']) && Sanitize::imageValidator($_FILES['picture'])){
       

        $name = $_FILES['picture']['name'];
        $tmp = $_FILES['picture']['tmp_name'];
        $location = '../../assets/images/';

        $newName = time().rand(10000,99999).'-'.$name;


        $DatabaseService = new DatabaseService;

        $connect = $DatabaseService->getConnection();

        $id = $_SESSION['id'];


        
        // sleep(5);


        if(move_uploaded_file($tmp, $location . $newName)){
            $stmt = $connect->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
            $stmt->execute(array($newName, $id));


            
            if($stmt->rowCount()){
                $response['message'] = 'Profile picture has been updated';
                $response['name'] = $newName;
                $response_code = 201;
                
            }    
        }


        echo json_encode($response);
        http_response_code($response_code);

    }else{
        echo var_dump(Sanitize::imageValidator($_FILES['picture']));
        header("HTTP/1.1 400 bad request");
    }
    
?>