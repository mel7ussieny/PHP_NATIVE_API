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


    $response = array("message" => null);
    $response_code = 400;


    if($_SERVER['REQUEST_METHOD'] == "GET"){

        $id = $_SESSION['id'];



        $DatabaseService = new DatabaseService;

        $connect = $DatabaseService->getConnection();

        try{
            $stmt = $connect->prepare("SELECT transactions.id, transactions.status , transactions.amount, transactions.payment_to, transactions.created_at , payment_methods.payment_name FROM transactions
            INNER JOIN payment_methods 
            ON transactions.payment_method = payment_methods.id
            WHERE 
            transactions.user_id = ? AND (transactions.status = 2 OR transactions.status = 0)
            ORDER BY transactions.id DESC
            ");
    
            $stmt->execute(array($id));
    
            $response['message'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response_code = 200;
    
        }catch(PDOException $e){
            header('HTTP/1.1 400 bad request');
        }


        
        echo json_encode($response);
        http_response_code($response_code);

    }else{
        header('HTTP/1.1 400 bad request');
    }

?>