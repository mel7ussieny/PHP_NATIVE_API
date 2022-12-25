<?php

session_start();
include_once '../../include/classes/DatabaseServiceClass.php';
include_once '../../include/classes/SanitizeClass.php';
include_once '../../include/classes/AuthClass.php';
include_once '../../include/classes/ExistsClass.php';




// @header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
// header("Content-Type: application/json; charset=UTF-8");
// header("Access-Control-Allow-Methods: POST");
// header("Access-Control-Max-Age: 3600");
// header("Access-Control-Allow-Credentials: true");
// header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");



// Auth::isAuth();


$params = ['get_task'];

$response = array("message" => array("details" => null, "images" => []));
$response_code = 200;
$validate = $_SERVER['REQUEST_METHOD'] == 'GET' &&  !empty($_GET['get_task']) && count($_GET) == 1 && filter_var($_GET['get_task'],FILTER_VALIDATE_INT) || $_GET['get_task'] == 'submitted'  ? true : false;



if($_SERVER['REQUEST_METHOD'] == 'GET' && $validate){   
    
    
    $email_condition = !empty($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) ? "users.email_address = '".$_GET['email']."'" : 1;
    $status_codition = isset($_GET['status']) && in_array($_GET['status'], [0,1,2]) ? 'user_tasks.submitted_status = ' . $_GET['status'] : 1;


    $databaseService = new DatabaseService ;
    $connect = $databaseService->getConnection();
    if($_GET['get_task'] == 'submitted'){

        try{
            $stmt = $connect->prepare("SELECT user_tasks.*, users.first_name, users.last_name, tasks.title, tasks.url_token FROM user_tasks 
            INNER JOIN users 
                ON user_tasks.user_id = users.user_id
            INNER JOIN tasks
                ON user_tasks.task_id = tasks.id
            WHERE 
            $email_condition 
            AND
            $status_codition
            ORDER BY submitted_at DESC
            ");
            $stmt->execute();
    
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['message'] = $rows;
            
        }catch(PDOException $e){

            echo $e->getMessage();

            
            $response['message'] = 'Server error';
            $response_code = 400;
        }
    }elseif(Exists::userTaskExists($_GET['get_task'])){

    
        
            try{
                $stmt = $connect->prepare("SELECT user_tasks.id, user_tasks.user_id, user_tasks.task_id, user_tasks.refused_reason, CONVERT(FROM_BASE64(user_tasks.description) using utf8) as 'description', user_tasks.submitted_at, user_tasks.submitted_status ,user_tasks_imgs.img_name, users.first_name, users.last_name, users.email_address FROM user_tasks INNER JOIN users ON user_tasks.user_id = users.user_id LEFT JOIN user_tasks_imgs ON user_tasks.id = user_tasks_imgs.user_task_id WHERE user_tasks.id = ?");
                $stmt->execute(array($_GET['get_task']));
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                

                if($stmt->rowCount()){
                    $details = array(
                        "id"                => $rows[0]["id"],
                        "user_id"           => $rows[0]['user_id'],
                        "task_id"           => $rows[0]['task_id'],
                        "description"       => $rows[0]['description'],
                        "submitted_at"      => $rows[0]['submitted_at'],
                        "submitted_status"  => $rows[0]['submitted_status'],
                        "first_name"        => $rows[0]['first_name'],
                        "last_name"         => $rows[0]['last_name'],
                        "email_address"     => $rows[0]['email_address'],
                        "reason"            => $rows[0]['refused_reason']
                    );
                    $response['message']['details'] = $details;
                    
                    $imgs = [];

                    foreach($rows as $key => $value){
                        if(!empty($rows[$key]['img_name']))
                            array_push($imgs, $rows[$key]['img_name']);
                    }

                    $response['message']['images'] = $imgs;
                }


                            
            }catch(PDOException $e){
                
                $response_code = 400;
            }
        
    }else{
        $response = array('message' => 'Task not found !');
        $response_code = 404;
    }

}


http_response_code($response_code);
echo json_encode($response);


?>