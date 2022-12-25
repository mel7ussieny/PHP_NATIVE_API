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
    

    // Auth::isAuth();



    $response = array('message' => null);

    $response_code = 400;


    $validate = $_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET['id']) && Exists::userIdExists($_GET['id']) ? true : false;



    if($validate){

        $response = array('message' => array('info' => null, 'transactions' => null, 'submitted' => null));

        $id = $_GET['id'];
    

        // Transactions 

        $DatabaseService = new DatabaseService;

        $connect = $DatabaseService->getConnection();

        try{
            $stmt = $connect->prepare("SELECT first_name, last_name, email_address, contact, created_at, user_activation, user_blocked FROM users WHERE user_id = ? LIMIT 1");
            $stmt->execute(array($id));

            $response['message']['info'] = $stmt->fetch(PDO::FETCH_ASSOC);


            $response_code = 200;


        }catch(PDOException $e){
            
            return false;
            
        }


        try{
            $stmt = $connect->prepare("SELECT transactions.id, transactions.amount, payment_methods.payment_name, transactions.payment_to, transactions.created_at, transactions.status, transactions.status_updated_at FROM transactions 
            INNER JOIN payment_methods 
            ON 
            transactions.payment_method = payment_methods.id
            WHERE user_id = ?");
            
            $stmt->execute(array($id));
            
            $response['message']['transactions'] = $stmt->fetchall(PDO::FETCH_ASSOC);

            $response_code = 200;
        }catch(PDOException $e){
            
            echo $e->getMessage();
            $response_code = 400;

        }

        try{
            $stmt = $connect->prepare("SELECT user_tasks.id, user_tasks.task_id, user_tasks.submitted_at, user_tasks.submitted_status, tasks.title, tasks.url_token
            FROM 
            user_tasks 
            INNER JOIN tasks 
            ON
            user_tasks.task_id = tasks.id 
            WHERE user_tasks.user_id = ?");
            $stmt->execute(array($id));
            
            $response['message']['submitted'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response_code = 200;
        }catch(PDOException $e){
            
            return false;

        }


        echo json_encode($response);
        http_response_code($response_code);

    }
?>