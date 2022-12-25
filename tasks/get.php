<?php
session_start();
include_once '../include/classes/DatabaseServiceClass.php';
include_once '../include/classes/SanitizeClass.php';
include_once '../include/classes/AuthClass.php';
include_once '../include/classes/ExistsClass.php';

@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] );
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");


Auth::isAuth();
$response = array("message" => "");
$response_code = 200;


$validate = $_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['token']) && count($_GET) == 1 && Exists::tokenExists($_GET['token']) ? true : false;




if($validate){
    $token = $_GET['token'];
    $databaseService = new DatabaseService;
    $connect = $databaseService->getConnection();


    
    try{
        $stmt = $connect->prepare("SELECT task_steps.task_id, task_steps.step, CONVERT(FROM_BASE64(task_steps.description) USING utf8) as 'description', task_steps.img_name, tasks.id, tasks.url_token ,tasks.title, CONVERT(FROM_BASE64(note) using utf8) as 'note',tasks.reward,tasks.status, tasks.created_at 
        FROM task_steps LEFT JOIN tasks 
        ON tasks.id = task_steps.task_id
        WHERE tasks.url_token = ?
        ");

        $stmt->execute(array($token));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = array(
            "task" => array(),
            "steps" => array(),
            "submitted" => 0
        );


        $task = array();
        $task['id']     = $rows[0]['task_id'];
        $task['title']  = $rows[0]['title'];
        $task['note']   = $rows[0]['note'];
        $task['reward'] = $rows[0]['reward'];


        $data['submitted'] = Exists::userTaskSubmitted($_SESSION['id'], $task['id']);
        $data['task'] = $task;
        $steps = array();

        for($x = 0 ; $x < count($rows); $x++){
            $step = array();      
            $step['step']           = $rows[$x]['step'];
            $step['description']    = $rows[$x]['description'];
            $step['img_name']       = $rows[$x]['img_name'];

            array_push($steps, $step);
        }

        $data['steps'] = $steps;
        
        $response['message'] = $data;

    }catch(PDOException $e){
        $response_code = 400;
        $response['message'] = 'Server error';
    }

    echo json_encode($response['message']);
}else{
    $response_code = 404;
    
}

http_response_code($response_code);



?>
