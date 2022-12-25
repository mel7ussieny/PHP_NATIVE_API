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



$response = array("message" => array(
    "tasks"     => null,
    "user_tasks" => null
));
$response_code = 200;



$pagination = !empty($_GET['pagination']) ? explode('__', $_GET['pagination']) : [];

$validate = $_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['action']) 

&& $_GET['action'] == 'view_tasks' 

&& count($_GET) <= 2 

&&count($pagination) == 2 

? true : false;


if($validate){   





    $offset = filter_var($pagination[0], FILTER_VALIDATE_INT) ? $pagination[0] : 0;
    
    $limit = filter_var($pagination[1], FILTER_VALIDATE_INT) ? $pagination[1] : 0;

    

    $databaseService = new DatabaseService ;
    $connect = $databaseService->getConnection();
    // $condition = !empty($_GET['q']) && Sanitize::sanitizeFormatText($_GET['q']) ? "title LIKE '" . $_GET['q'] . "%'": 1; 
       

    $tasks = [];

    $userTasks = [];
        try{

             
            $stmt = $connect->prepare("SELECT tasks.id, tasks.title, CONVERT(FROM_BASE64(tasks.note) using utf8) as 'note', tasks.reward, tasks.status, tasks.url_token, tasks.created_at, submitted.status_updated_at, submitted.submitted_status, submitted.submitted_at 
            FROM tasks 
            LEFT JOIN (SELECT user_tasks.id, user_tasks.submitted_status, user_tasks.task_id, user_tasks.status_updated_at, user_tasks.submitted_at 
            FROM user_tasks WHERE user_tasks.user_id = :user_id) submitted 
            ON tasks.id = submitted.task_id 
            WHERE submitted.submitted_status IS NULL
            ORDER BY tasks.id DESC
            LIMIT :limits OFFSET :offset
            ");

            $stmt->bindValue(':user_id', $_SESSION['id'], PDO::PARAM_INT);
            $stmt->bindValue(':limits', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_UNIQUE);
            
            
        }catch(PDOException $e){

            echo $e->getMessage();

            $response_code = 400;
            $response['message'] = "server error !";
        }



        try{
            $stmt = $connect->prepare("SELECT tasks.id, tasks.title, CONVERT(FROM_BASE64(tasks.note) using utf8) as 'note', tasks.reward, tasks.status, user_tasks.submitted_status ,tasks.url_token, tasks.created_at 
            FROM tasks
            INNER JOIN user_tasks 
            ON tasks.id = user_tasks.task_id 
            WHERE user_tasks.user_id = ?
            ");
            $stmt->execute(array($_SESSION['id']));

            $userTasks = $stmt->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC);

            // print_r($userTasks);
        }catch(PDOException $e){
            echo $e->getMessage();
        }

    


        $response['message']['tasks'] = $tasks;
        $response['message']['user_tasks'] = $userTasks;
    
        


}

http_response_code($response_code);
echo json_encode($response);


?>