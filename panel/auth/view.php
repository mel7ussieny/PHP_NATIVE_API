<?php

session_start();


@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");


include_once '../../include/classes/DatabaseServiceClass.php';
include_once '../../include/classes/SanitizeClass.php';
include_once '../../include/classes/AuthClass.php';



Auth::isAuth();


$response = array("message" => 0);
$response_code = 200;

$validate = $_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['action']) && $_GET['action'] == 'view' && count($_GET) == 1 ? true : false;


if($validate){

    $DatabaseService = new DatabaseService;
    $connect = $DatabaseService->getConnection();
    
    try{ 
        $stmt = $connect->prepare("SELECT user_id, first_name, last_name, email_address, created_at FROM roles");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $response['message'] = $rows;

    }catch(PDOException $e){
        $response_code = 400;
        $response['message'] = 'Server error!';
    }

    echo json_encode($response);
    http_response_code($response_code);
    
}else{
    header('HTTP/1.1 400 bad request');
}

?>