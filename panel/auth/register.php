<?php

session_start();


include_once '../../include/classes/DatabaseServiceClass.php';
include_once '../../include/classes/SanitizeClass.php';
include_once '../../include/classes/AuthClass.php';
include_once '../../include/classes/ExistsClass.php';



@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");


Auth::isAuth();

$validate = false;
$data = Sanitize::jsonValidator(file_get_contents("php://input"));
$response = array("message" => 0);
$response_code = 200;
$arr_param = ['first_name', 'last_name','email','password'];




if($_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 4){
    $validate = true;

    foreach($arr_param as $key => $value){
        if(empty($data[$value])){
            $response = array("message" => "Missing field ". $value);
            $validate = false;
            break;
        }
    }
}



if($validate){  
    
    if(!Exists::rolesEmailExists($data['email'])){

        $DatabaseService = new DatabaseService;
        
        $connect = $DatabaseService->getConnection();

        $password = password_hash($data['password'], PASSWORD_BCRYPT);


        $stmt = $connect->prepare("INSERT INTO roles (first_name, last_name, email_address, password) VALUES (:first, :last, :email, :password)");
        $stmt->execute(array(
            "first"     => $data['first_name'],
            "last"      => $data['last_name'],
            "email"     => $data['email'],
            "password"  => $password
        ));
        

        if($stmt->rowCount()){
            
            
            $response_code = 201;
            $response['message'] = 'Admin has been added successfully';

            
        }else{
            $response_code = 400;
            $response['message'] = 'Unknown error while adding admin to DB';
        }
    }else{
        $response['message'] = 'Email address already exists';
        $response_code = 400;
    }
    

    echo json_encode($response);
    http_response_code($response_code);
}else{
    header("HTTP/1.1 400 bad request");
}




















?>