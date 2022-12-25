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

    $data = !empty(file_get_contents('php://input', true)) ? Sanitize::jsonValidator(file_get_contents('php://input',true)) : [];
    $arr_param = ['service', 'required'];

    $response = array("message" => "");
    $response_code = 200;

    
    $validate = false;
    if($_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 2){
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
       $service     = filter_var($data['service'], FILTER_SANITIZE_STRING);
       $required    = filter_var($data['required'], FILTER_SANITIZE_STRING);
       
        $DatabaseService = new DatabaseService ;
        $connect = $DatabaseService->getConnection();


        try{
            $stmt = $connect->prepare("INSERT INTO payment_methods(payment_name, required) VALUES (:pname, :prequired)");
            $stmt->execute(array(
                "pname" => $service,
                "prequired" => $required
            ));
            $response['message'] = 'Payment has been added successfully';
        }catch(PDOException $e){
            $response['message'] = 'Error while adding payment to DB';
            $response_code = 400;
        }
        
        echo json_encode($response);
        http_response_code($response_code);
    }else{
        header("HTTP/1.1 400 bad request");
    }



?>