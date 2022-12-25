<?php

session_start();


include_once '../include/classes/AuthClass.php';
include_once '../include/classes/DatabaseServiceClass.php';
include_once '../include/classes/SanitizeClass.php';

@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
@header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


Auth::isAuth();

$data = !empty(file_get_contents('php://input', true)) ? Sanitize::jsonValidator(file_get_contents('php://input',true)) : [];


$arr_param = [ 'payment_id', 'payment_amount', 'sending_to'];

$response = array("message" => "bad request");
$response_code = 200;

$validate = false;

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


    $payment_id = filter_var($data['payment_id'], FILTER_VALIDATE_INT) ?  $data['payment_id'] : 0;
    $amount     = filter_var($data['payment_amount'], FILTER_VALIDATE_INT) ? $data['payment_amount'] : 0;
    $user_id    = $_SESSION['id'];
    $sending_to = filter_var($data['sending_to'], FILTER_SANITIZE_STRING);

    $DatabaseService = new DatabaseService;
    $connect = $DatabaseService->getConnection();


    try{
        $stmt = $connect->prepare("SELECT (SELECT SUM(amount) FROM transactions WHERE user_id = ? AND status = 1 ) AS cashout, 
        (SELECT SUM(reward) FROM tasks INNER JOIN user_tasks ON tasks.id = user_tasks.task_id WHERE user_tasks.user_id = ? AND submitted_status = 1 ) AS deposite, 
        (SELECT SUM(amount) FROM transactions WHERE user_id = ? AND status = 2) reviewing");
        $stmt->execute(array($user_id, $user_id, $user_id));
        $balance = $stmt->fetch(PDO::FETCH_ASSOC);

        $actual_balance = $balance['deposite'] - ($balance['reviewing'] + $balance['cashout']);
    
        if($amount > $actual_balance){
            $response['message'] = 'Your balance is not enough to withdraw';
            echo json_encode($response);
            header("HTTP/1.1 400");
            exit;    
        }
        
    }catch(PDOException $e){
        echo json_encode($response);
        header("HTTP/1.1 400");
        exit;    
    }

    try{

        $stmt = $connect->prepare("INSERT INTO transactions(amount, user_id, payment_method, payment_to) VALUE(:amount, :usrId, :py_m, :py_t)");
        $stmt->execute(array(
            "amount"    => $amount,
            "usrId"     => $user_id,
            "py_m"     => $payment_id,
            "py_t"      => $sending_to
        ));


        $response['message'] = 'Your transaction has been sent';
        $response_code = 200;    
        
    }catch(PDOException $e){
        $response['message'] = 'Your transaction has been denied';
        $response_code = 400;
    }


    echo json_encode($response);
    http_response_code($response_code);

}else{
    echo json_encode($response);
    header("HTTP/1.1 400 bad request");
}


?>