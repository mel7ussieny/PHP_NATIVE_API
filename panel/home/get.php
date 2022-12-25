<?php
session_start();

include_once '../../include/classes/AuthClass.php';
include_once '../../include/classes/DatabaseServiceClass.php';

@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");


Auth::isAuth();

$response = array("message" => "");
$response_code = 200;

$validate = $_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['view']) && count($_GET) == 1 && $_GET['view'] == 'home' ? true : false;


if($validate){
 
    $DatabaseService = new DatabaseService();
    $connect = $DatabaseService->getConnection();
    
    $data = array(
        "analysis" => array(),
        "tasks" => array()
    );
    try{
        $stmt = $connect->prepare("SELECT (SELECT COUNT(user_id) FROM users) as users, (SELECT COUNT(id) FROM tasks) as tasks, (SELECT COUNT(id) FROM user_tasks) as submitted");
        $stmt->execute();
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['analysis'] = $rows;
        
        $stmt2 = $connect->prepare("SELECT tasks.id, tasks.title, tasks.added_by, tasks.status, tasks.url_token, tasks.created_at, roles.user_id, roles.first_name, roles.last_name FROM tasks INNER JOIN roles ON tasks.added_by = roles.user_id ORDER BY tasks.created_at DESC LIMIT 10");
        $stmt2->execute();
        $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $data['tasks'] = $rows;
        
        $response['message'] = $data;
    }catch(PDOException $e){
        echo $e->getMessage();

        $response_code = 400;
        $response['message'] = 'Server error';
    }

}

echo json_encode($response['message']);
http_response_code($response_code);




?>