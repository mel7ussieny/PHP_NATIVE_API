<?php
session_start();

include_once '../../include/classes/DatabaseServiceClass.php';
include_once '../../include/classes/SanitizeClass.php';
include_once '../../include/classes/AuthClass.php';
include_once '../../include/classes/ExistsClass.php';

@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");


Auth::isAuth();

$validate = false;
$data = !empty($_POST['data']) ? Sanitize::jsonValidator($_POST['data']) : [];
$response = array("message" => "Server error");
$response_code = 200;
$arr_param = ['title','steps','dates','reward'];






if($_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 5){
    $validate = true;
    foreach($arr_param as $key => $value){
        if(empty($data[$value]) && !(is_array($data['dates']))){
            $response = array("message" => "Missing field ". $value);
            $validate = false;
            break;
        }
    }
    if(count($_FILES) > 0 && !(Sanitize::imageValidator($_FILES['step_imgs']))){
        $validate = false;
    }

    if(count($_FILES['step_imgs']['name']) !== count($_POST['step_descriptions'])){
        $validate = false;
        $response['message'] = 'Steps not equivalent';
    }
}



if($validate){
    
    $databaseService = new DatabaseService();
    $connect = $databaseService->getConnection();



    $title           = filter_var($data['title'], FILTER_SANITIZE_STRING);
    $note            = base64_encode($data['note']);
    $reward          = filter_var($data['reward'], FILTER_VALIDATE_INT) ? $data['reward'] : 0 ;
    $role            = $_SESSION['id'];
    

    $started_at = count($data['dates']) == 2 ? $data['dates'][0] : NULL;
    $expired_at = count($data['dates']) == 2 ? $data['dates'][1] : NULL;


    $taskCreated = false;
    


    do{
        $token = bin2hex(random_bytes(10));

    }while(Exists::tokenExists($token));
    
    

    try{
        $stmt = $connect->prepare("INSERT INTO tasks(title, note, added_by, reward,  url_token, started_at, ended_at) VALUES (:title, :note, :added, :rew, :token,:st_at, :en_at)");
        $stmt->execute(array(
            "added" => $role,
            "title" => $title,
            "note"  => $note,
            "rew"   => $reward,
            "token" => $token,
            "st_at" => $started_at,
            "en_at" => $expired_at
        ));

        $response_code = 201;
        // http_response_code(201);
        $response['message'] = 'Task has been added successfully';
        
        $taskCreated = true;
    }catch(PDOException $e){
        $response_code = 400;
        echo $e->getMessage();
        // http_response_code(400);
        $response['message'] = 'Task has been rejected from server';
        
    }

    if(count($_FILES['step_imgs']['name']) > 0 && $taskCreated){

        $task_id = $connect->lastInsertId();
        $x = 0;
        
        try{            
            while($x < count($_FILES['step_imgs']['name'])){
    
                $name   = $_FILES['step_imgs']['name'][$x];
                $tmp    = $_FILES['step_imgs']['tmp_name'][$x];
                $location = '../../../assets/images/';
                $newName =  time() . rand(10000,99999) . '-' . $name;
    
                if(move_uploaded_file($tmp, $location . $newName)){
                    $stmt = $connect->prepare("INSERT INTO task_steps(task_id, step, description , img_name) VALUES (:id ,:step, :desc,:img)");
                    $stmt->execute(array(
                        "id"    => $task_id,
                        "desc"  => base64_encode(Sanitize::sanitizeFormatDescription($_POST['step_descriptions'][$x])),
                        "step"  => $x +1,
                        "img"   => $newName
                    ));
          
                }
                $x++;
            }
            $response_code = 201;
            $response['message'] = 'Task has been added successfully';
        }catch(PDOException $e){
            $response_code = 200;
            $response['message'] = 'Unable to upload images to server';
        }

    }

}

http_response_code($response_code);
echo json_encode($response);


?>