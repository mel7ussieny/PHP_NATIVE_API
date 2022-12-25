<?php
    
    session_start();

    
    include_once '../include/classes/AuthClass.php';
    include_once '../include/classes/DatabaseServiceClass.php';
    
    @header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Credentials: true");
    @header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
    
    Auth::isAuth();



    $response = array("message" => "");
    $response_code = 200;
    

        

        try{
            $DatabaseService = new DatabaseService;
            $connect = $DatabaseService->getConnection();
            
            $stmt = $connect->prepare("SELECT id, payment_name, required, required FROM payment_methods");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $response['message'] = $rows;

        }catch(PDOException $e){
            $response['message'] = 'Server error!';
            $response_code = 400;
        }
        

        echo json_encode($response);
        http_response_code($response_code);


       



?>