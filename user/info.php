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



// $validate = $_SERVER['REQUEST_METHOD'] == 'GET' && count($_GET) == 1 && !empty($_GET['id']) && Exists::userIdExists($_GET['id']) && $_GET['id'] == $_SESSION['id'] ? true : false;



$validate = !empty($_SESSION['id']) && Exists::userIdExists($_SESSION['id']);

$response = array("message" => array("user" => null , "tasks" => null));
$response_code = 200;


if($validate){

    $id = $_SESSION['id'];

    try{
        $DatabaseService = new DatabaseService;

        $connect = $DatabaseService->getConnection();

        $stmt = $connect->prepare("SELECT first_name, last_name, email_address, contact ,country, created_at, user_activation FROM users WHERE user_id = ?");
        $stmt->execute(array($id));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['message']['user'] = $user;

        $stmt = $connect->prepare("SELECT user_tasks.id, user_tasks.user_id, user_tasks.task_id, user_tasks.submitted_at, user_tasks.refused_reason , user_tasks.submitted_status,
        tasks.id, tasks.url_token, tasks.title 
        FROM user_tasks
        INNER JOIN tasks 
        ON user_tasks.task_id = tasks.id WHERE user_tasks.user_id = ? ORDER BY user_tasks.submitted_at DESC");
        $stmt->execute(array($id));
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['message']['tasks'] = $tasks;
        
    }catch(PDOException $e){
        $response['message'] = "Server error";
        $response_code = 400;
    }

}

echo json_encode($response);
http_response_code($response_code);


?>
