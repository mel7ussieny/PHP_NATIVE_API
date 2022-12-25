<?php
// SELECT transactions.id, transactions.amount, transactions.payment_to, transactions.created_at, transactions.status, transactions.status_updated_at, users.first_name AS user_first_name, users.last_name AS user_last_name, users.email_address, users.country , roles.first_name AS role_first_name, roles.last_name AS role_last_name, payment_methods.payment_name FROM transactions INNER JOIN payment_methods ON transactions.payment_method = payment_methods.id LEFT JOIN users ON transactions.user_id = users.user_id LEFT JOIN roles ON transactions.admin_id = roles.user_id;

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


// sleep(3);

Auth::isAuth();

$response = array("message" => "");
$response_code = 200;

$validate = $_SERVER['REQUEST_METHOD'] == "GET";



if($validate){

    $DatabaseService = new DatabaseService;
    $connect = $DatabaseService->getConnection();

    
    try{
        $stmt = $connect->prepare("SELECT transactions.id, transactions.amount, transactions.payment_to, transactions.created_at, transactions.status, transactions.status_updated_at, users.first_name AS user_first_name, users.last_name AS user_last_name, users.email_address, users.country , roles.first_name AS role_first_name, roles.last_name AS role_last_name, payment_methods.payment_name 
        FROM transactions INNER JOIN payment_methods 
        ON transactions.payment_method = payment_methods.id 
        LEFT JOIN users 
        ON transactions.user_id = users.user_id 
        LEFT JOIN roles 
        ON transactions.admin_id = roles.user_id
        ORDER BY transactions.created_at DESC
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response_code = 200;
        $response['message'] = $rows;
    }catch(PDOException $e){
        $response_code = 400;
        $response['message'] = 'Server error!';
    }
    

    echo json_encode($response);
    http_response_code($response_code);

}else{
    header("HTTP/1.1 400 bad request");
}







?>