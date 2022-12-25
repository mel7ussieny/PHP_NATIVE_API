<?php

session_start();

include_once '../../include/classes/AuthClass.php';
include_once '../../include/classes/DatabaseServiceClass.php';
include_once '../../include/classes/SanitizeClass.php';

@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");


Auth::isAuth();


sleep(3);

$response_code = 200;
$response = array('message' => '');

$data = Sanitize::jsonValidator(file_get_contents("php://input"));


$validate = $_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 1 && !empty($data['user_id']) && filter_var($data['user_id'], FILTER_VALIDATE_INT) ? TRUE : FALSE;



if($validate){

    $id = $data['user_id'];
    $DatabaseService = new DatabaseService;
    $connect = $DatabaseService->getConnection();

    try{
        $stmt = $connect->prepare("DELETE FROM roles WHERE user_id = ?");
        $stmt->execute(array($id));

        
        if($stmt->rowCount()){
            $response_code = 200;
            $response['message'] = 'Admin has been deleted successfully';
        }else{
            $response_code = 400;
            $response['message'] = 'Uknown error while deleting admin from DB';
        }

    }catch(PDOException $e){
        $response['message'] = 'Error while deleting admin from DB';
        $response_code = 400;
    }

    echo json_encode($response);
    http_response_code($response_code);
}else{
    header('HTTP/1.1 400 bad request');
}



?>