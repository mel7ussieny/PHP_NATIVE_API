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


$data = !empty(file_get_contents('php://input',true)) ? Sanitize::jsonValidator(file_get_contents('php://input',true)) : [];
$validate = $_SERVER['REQUEST_METHOD'] == "POST" && count($data) == 3;
$response = array("message" => "Server error");
$response_code = 200;
$arr_param = ['active','blocked','note'];



foreach($arr_param as $key => $value){
    if(!isset($data[$value])){
        $response = array("message" => "Missing field ". $value);
        $response_code = 400;
        $validate = false;
        break;
    }
}


if($validate){

        
    $DatabaseService = new DatabaseService;

    $connect = $DatabaseService->getConnection();


    try{
        
        $stmt = $connect->prepare('UPDATE users SET user_blocked = ? , user_activation = ? , note = ? WHERE user_id = ?');

        $stmt->execute(array($data['blocked'], $data['active'], $data['note']));
        $response['message'] = 'User profile has been updated successfully';
        $response_code = 200;
    
    }catch(PDOException $e){
        
        $response['message'] = 'Error while updating user';
        $response_code = 400;
    
    }


}else{
    print_r($response);
}



?>
