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




    $data = !empty(file_get_contents('php://input')) ? Sanitize::jsonValidator(file_get_contents('php://input')) : [];

    $validate = $_SERVER['REQUEST_METHOD'] == 'POST' 
    && !empty($data['status']) && filter_var($data['status'], FILTER_VALIDATE_INT)
    && !empty($data['ticked_id']) && filter_var($data['ticked_id'], FILTER_VALIDATE_INT) ? true : false;
    
    

    if($validate){

        $id = $data['ticked_id'];
        $status = $data['status'];

        $DatabaseService = new DatabaseService; 

        $connect = $DatabaseService->getConnection();

        try{
            $stmt = $connect->prepare("UPDATE support_tickets SET status = ? WHERE id = ?");
            $stmt->execute(array($status, $id));

            $response_code = 200;
            $response['message'] = 'Ticket has been updated';
        }catch(PDOException $e){
            $response['message'] = "Problem while updating ticket status";
        }

    }

    echo json_encode($response);
    http_response_code($response_code);

?>
