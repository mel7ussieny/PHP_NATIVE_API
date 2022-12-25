<?php
session_start();
include_once '../include/classes/DatabaseServiceClass.php';
include_once '../include/classes/SanitizeClass.php';
include_once '../include/classes/AuthClass.php';
include_once '../include/classes/ExistsClass.php';

@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");

Auth::isAuth();



$response = array('message' => 'Problem occured while opening ticket');

$response_code = 400;

$data = Sanitize::jsonValidator(file_get_contents("php://input"));



$validate = $_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 2 && !empty($data['title']) && !empty($data['description']) ? true : false;


if($validate){
    
    $DatabaseService = new DatabaseService;

    $connect = $DatabaseService->getConnection();




    $id = $_SESSION['id'];


    try{
        $stmt = $connect->prepare("SELECT contact FROM users WHERE user_id = ? LIMIT 1");
        
        $stmt->execute(array($id));
        

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    


    }catch(PDOException $e){
        return false;
    }


    $contacts = json_decode($row['contact'], true) ? json_decode($row['contact'], true) : array(); 


    if(count($contacts)){

        $title = filter_var($data['title'], FILTER_SANITIZE_STRING);
    
        $description    = base64_encode(Sanitize::sanitizeFormatDescription($data['description']));

        $stmt = $connect->prepare("INSERT INTO support_tickets (user_id, title, description) VALUES (:user, :title, :description)");

        $stmt->execute(array(
            "user"          => $_SESSION['id'],
            "title"         => $title,
            "description"   => $description 
        ));


        if($stmt->rowCount()){
            $response['message'] = 'Your ticket has been opened';
            $response_code = 201;
        }else{
            $response['message'] = 'Problem occured while opening ticket';
            $response_code = 400;
        } 
    
    }else{
        $response['message'] = 'Please set at least one contact method';
        $response_code = 400;
    }




    
}

echo json_encode($response);
http_response_code($response_code);



?>