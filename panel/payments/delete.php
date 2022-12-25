<?php

session_start();
include_once '../../include/classes/AuthClass.php';
include_once '../../include/classes/DatabaseServiceClass.php';
include_once '../../include/classes/SanitizeClass.php';

@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");


Auth::isAuth();

$data = !empty(file_get_contents('php://input', true)) ? Sanitize::jsonValidator(file_get_contents('php://input', true)) : [];
$arr_param = ['payment_id'];

$response = array("message" => "");
$response_code = 200;
$validate = false;


if($_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 1 ){
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

    $id = !empty($data['payment_id']) && filter_var($data['payment_id'], FILTER_VALIDATE_INT) ? $data['payment_id'] : 0;

    $DatabaseService = new DatabaseService;
    $connect = $DatabaseService->getConnection();

    try{
        $stmt = $connect->prepare("DELETE FROM payment_methods WHERE id = ?");
        $stmt->execute(array($id));
        $count = $stmt->rowCount();
        $response['message'] = $count ? 'Payment has been deleted successfully' : 'Error while deleting payment from DB';
        $response_code = $count ? 200 : 400;
    }catch(PDOException $e){

        $response['message'] = 'Uknown server error';
        $response_code = 400;
    }

    echo json_encode($response);
    http_response_code($response_code);
}else{
    header("HTTP/1.1 400 bad request");
}
?>
