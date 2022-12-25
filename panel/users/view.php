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


    $response = array('message' => []);
    $response_code = 400;



    $validate = $_SERVER['REQUEST_METHOD'] == "GET";


    $verified   = isset($_GET['verified']) && in_array($_GET['verified'], [0,1]) ? 'user_activation = ' . $_GET['verified'] : 1;
    $email      = !empty($_GET['email']) && filter_var($_GET['email'], FILTER_VALIDATE_EMAIL) ? 'email_address = "' . $_GET['email'] .'"' : 1; 
    

    if($validate){
        
        $DatabaseService = new DatabaseService;

        $connect = $DatabaseService->getConnection();

    

        try{
            $stmt = $connect->prepare("SELECT user_id, first_name, last_name, email_address, contact, country, created_at, user_activation, user_blocked FROM users WHERE $email AND $verified  LIMIT 10");

            $stmt->execute();
            $response['message'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response_code = 200;

        }catch(PDOException $e){
          
            $response_code = 400;
            $response['message'] = 'Error while loading users';
        }
        
    }
    
    echo json_encode($response);
    http_response_code($response_code);



?>