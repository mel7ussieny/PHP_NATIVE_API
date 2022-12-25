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


$validate = $_SERVER['REQUEST_METHOD'] == 'POST' && Exists::userIdExists($_SESSION['id']) ? true : false;
$data = Sanitize::jsonValidator(file_get_contents("php://input"));




$response = array("message" => "");
$response_code = 400;


if($validate){


    $oldPass = $data['oldPassword'];
    $newPass = $data['newPassword'];
    $id = $_SESSION['id'];
    $DatabaseService = new DatabaseService;

    $connect = $DatabaseService->getConnection();

    try{
        $stmt = $connect->prepare("SELECT user_id, password FROM users WHERE user_id = ?");
        $stmt->execute(array($id));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);        


        if(password_verify($oldPass, $user['password'])){

            $hashed = password_hash($newPass, PASSWORD_BCRYPT);


            if(!password_verify($newPass, $user['password'])){

                $stmtUpdate = $connect->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmtUpdate->execute(array($hashed, $id));
                $response['message'] = 'Password has been updated';
                $response_code = 200;
            }else{
                $response['message'] = 'New password cannot be the same as your old password';
                $response_code = 400;
            }


        }else{

            $response['message'] = 'Old password is incorrect';
            $response_code = 400;
        }



    }catch(PDOException $e){
        $response['message'] = 'Server error';
        $response_code  = 400;
    }

    echo json_encode($response);

    http_response_code($response_code);

}else{
    header("HTTP/1.1 400 bad request");
}










?>