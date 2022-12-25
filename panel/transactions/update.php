<?php
session_start();
include_once '../../include/classes/DatabaseServiceClass.php';
include_once '../../include/classes/SanitizeClass.php';
include_once '../../include/classes/AuthClass.php';
include_once '../../include/classes/ExistsClass.php';

@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");


Auth::isAuth();

$data = !empty(file_get_contents("php://input", true)) ? Sanitize::jsonValidator(file_get_contents("php://input", true)) : [];

$arr_param = ['id', 'status'];


$response = array("message" => "");
$response_code = 200;

$validate = false;


if($_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 2){
    $validate = true;

    // Valid Range


    foreach($arr_param as $key => $value){
        if(!isset($data[$value])){
            $response = array("message" => "Missing field ". $value);
            $response_code = 400;
            $validate = false;
            break;
        }
    }
}



if($validate){

    $id = filter_var($data['id'], FILTER_VALIDATE_INT) ? $data['id'] : 0;
    $status = in_array($data['status'], range(0,2)) ? $data['status'] : 0;
    $date = Date("Y-m-d H:i:s");
    $role_id = $_SESSION['id'];

    $DatabaseService = new DatabaseService;
    $connect = $DatabaseService->getConnection();


    try{
        $stmt = $connect->prepare("UPDATE transactions SET status = ?, admin_id = ?, status_updated_at = ? WHERE id = ?");
        $stmt->execute(array($status ,$role_id, $date, $id));

        if($stmt->rowCount()){
            $response['message'] = 'Transaction has been updated successfully';
            $response_code = 200;
        }else{
            $response['message'] = 'Couldn\'t update transaction';
            $response_code = 400;
        }
    }catch(PDOException $e){
        $response['message'] = 'Server error';
        $response_code = 400;
    }

    
    echo json_encode($response);
    http_response_code($response_code);


}else{
    header("HTTP/1.1 400 bad request");
}


    


?>