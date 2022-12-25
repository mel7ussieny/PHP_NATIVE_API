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


    $response = array('message' => 0);
    $response_code = 400;


    $validate = $_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['status']) ? true : false;


    if($validate){
    

        $condition = !empty($_GET['status']) ? 'status = ' . $_GET['status'] : 1;

        $DatabaseService = new DatabaseService;


        $connect = $DatabaseService->getConnection();


        try{
            $stmt = $connect->prepare("SELECT 
            support_tickets.id, support_tickets.title, CONVERT(FROM_BASE64(support_tickets.description) using utf8) as 'description', support_tickets.created_at, support_tickets.status
            , users.first_name, users.last_name, users.email_address, users.contact 
            FROM 
            support_tickets
            INNER JOIN users 
            ON support_tickets.user_id = users.user_id
            WHERE $condition
            ORDER BY support_tickets.id DESC
            ");
            $stmt->execute();
            $response_code = 200;
            $response['message'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        }catch(PDOException $e){
            // echo $e->getMessage();
            return false;
        }

    }
            
    echo json_encode($response);
    http_response_code($response_code);


?>