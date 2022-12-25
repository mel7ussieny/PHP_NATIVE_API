<?php
session_start();
include_once '../include/classes/DatabaseServiceClass.php';
include_once '../include/classes/SanitizeClass.php';
include_once '../include/classes/AuthClass.php';
include_once '../include/classes/ExistsClass.php';

@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");

Auth::isAuth();


$response = array("message" => '');
$response_code = 200;

$validate = $_SERVER['REQUEST_METHOD'] == 'GET' ? true : false;


if($validate){
    
    $databaseService = new DatabaseService();
    
    $connect = $databaseService->getConnection();
    $id = filter_var($_SESSION['id'], FILTER_VALIDATE_INT) ? $_SESSION['id'] : 0;
    
    try{
        
        $stmt = $connect->prepare("SELECT 
        (SELECT count(id) FROM user_tasks WHERE user_id = $id) AS 'submitted', 
        (SELECT count(id) FROM user_tasks WHERE submitted_status = 2 AND user_id = $id) AS 'reviews', 
        (SELECT count(id) FROM user_tasks WHERE submitted_status = 1 AND user_id = $id) AS 'accepted'");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response['message'] = $row;
    
    }catch(PDOException $e){
        $response_code = 400;
        $response['message'] = "Server error";
    }
}

echo json_encode($response);
http_response_code($response_code);


?>