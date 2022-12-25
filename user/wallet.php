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



$response = array("message" => array("wallet" => null , "transactions" => null));
$response_code = 200;


if($_SERVER['REQUEST_METHOD'] == "GET" && !empty($_SESSION['id']) && Exists::userIdExists($_SESSION['id'])){

    $id = $_SESSION['id'];
    $user = null;
    $transactions = null;
    
    $DatabaseService = new DatabaseService;
    $connect = $DatabaseService->getConnection();

    $paidTasks = [];
    $transactions = [];

    try{

        $stmt = $connect->prepare("SELECT (SELECT SUM(amount) FROM transactions WHERE user_id = ? AND status = 1 ) AS cashout, 
        (SELECT SUM(reward) FROM tasks INNER JOIN user_tasks ON tasks.id = user_tasks.task_id WHERE user_tasks.user_id = ? AND submitted_status = 1 ) AS deposite, 
        (SELECT SUM(amount) FROM transactions WHERE user_id = ? AND status = 2) reviewing");
        $stmt->execute(array($id, $id, $id));
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $response['message']['wallet'] = $data['deposite'] - $data['cashout'];
        $response['message']['virtualWallet'] = $data['deposite'] -  ($data['cashout'] + $data['reviewing']);
    
        try{
            $stmt = $connect->prepare("SELECT user_tasks.status_updated_at AS date, user_tasks.task_id,tasks.reward AS amount
            FROM user_tasks 
            INNER JOIN tasks 
            ON user_tasks.task_id = tasks.id
            WHERE user_tasks.user_id = ? AND user_tasks.submitted_status = 1
            ");
            $stmt->execute(array($id));
            $paidTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            try{
                $stmt = $connect->prepare("SELECT transactions.id, transactions.amount, transactions.payment_to, payment_methods.payment_name ,transactions.status_updated_at as date FROM transactions 
                INNER JOIN payment_methods 
                ON transactions.payment_method = payment_methods.id
                WHERE user_id = ? AND status = 1");
                $stmt->execute(array($id));
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }catch(PDOException $e){
                $response['message'] = '';
                $response_code = 400;
            }
    
        }catch(PDOException $e){
            $response['message'] = '';
            $response_code = 400;
        }
    }catch(PDOException $e){
        $response['message'] = '';
        $response_code = 400;
    }


    



    $mixedArray = array_merge($paidTasks, $transactions);
    
    usort($mixedArray, function($a, $b){
        if($a == $b){
            return 0;
        }
        

        return ($a['date'] < $b['date']) ? -1 : 1;
    });

    $mixedArray = array_reverse($mixedArray);

    

    if(count($mixedArray) > 0){
        $response['message']['transactions'] = $mixedArray;
    }


    echo json_encode($response);
    http_response_code($response_code);
    

}else{
    header("HTTP/1.1 400 bad request");
}

?>