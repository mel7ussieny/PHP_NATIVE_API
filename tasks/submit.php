<?php
session_start();
include_once '../include/classes/DatabaseServiceClass.php';
include_once '../include/classes/SanitizeClass.php';
include_once '../include/classes/AuthClass.php';
include_once '../include/classes/ExistsClass.php';

@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");



Auth::isAuth();



$validate = false;
$data = Sanitize::jsonValidator($_POST['data']);
$response = array("message" => 0);
$response_code = 200;
$arr_param = ['user_id','task_id','description'];



if($_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 3){
    $validate = true;

    foreach($arr_param as $key => $value){
        if(empty($data[$value])){
            $response = array("message" => "Missing field ". $value);
            $response_code = 400;
            $validate = false;
            break;
        }
    }

}

if($validate){

    if(!empty($_FILES['files'])){
        $_FILES['files'] = Sanitize::imageValidator($_FILES['files']) ? $_FILES['files'] : [];
    }

    

    $description    = base64_encode(Sanitize::sanitizeFormatDescription($data['description']));
    $task_id        = Sanitize::sanitizeInteger($data['task_id']); 
    $user_id        = Sanitize::sanitizeInteger($data['user_id']);

    $DatabaseService = new DatabaseService();
    $connect = $DatabaseService->getConnection();

    $taskSubmitted = false;

    if($task_id && $user_id){
        try{
            $stmt = $connect->prepare("INSERT INTO user_tasks(user_id, task_id, description) VALUES (:usr_id, :tsk_id, :desc)");
            $stmt->execute(array(
                "usr_id"    => $user_id,
                "tsk_id"    => $task_id,
                "desc"      => $description  
            ));

            $response_code = 201;
            $response['message'] = "Your submission has been received";
            $taskSubmitted = true;
        }catch(PDOException $e){
            $response_code = 400;

            $response['message'] = 'Your submission has been rejected';
        }

        if(!empty($_FILES['files']) && $_FILES['files']['name'] > 0 && $taskSubmitted){
            $submitted_id = $connect->lastInsertId();
            $x = 0;

            try{
                while($x < count($_FILES['files']['name'])){
                    $name = $_FILES['files']['name'][$x];
                    $tmp = $_FILES['files']['tmp_name'][$x];
                    $location = '../../assets/images/';

                    $newName = time().rand(10000,99999).'-'.$name;

                    if(move_uploaded_file($tmp, $location . $newName)){
                        $stmt = $connect->prepare("INSERT INTO user_tasks_imgs(user_task_id, img_name) VALUES (:usr_tsk_id, :img_name)");
                        $stmt->execute(array(
                            "usr_tsk_id" => $submitted_id,
                            "img_name"  => $newName
                        ));
                    }
                    $x++;
                }
            }catch(PDOException $e){
                $response_code = 400;
                $response['message'] = "Unable to upload images to server";      
            }
        }
        
    }
    
}


echo json_encode($response);
http_response_code($response_code);

?>