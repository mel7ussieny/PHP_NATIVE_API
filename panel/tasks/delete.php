<?php
    session_start();
    include_once '../../include/classes/DatabaseServiceClass.php';
    include_once '../../include/classes/SanitizeClass.php';
    include_once '../../include/classes/AuthClass.php';
    include_once '../../include/classes/ExistsClass.php';

    

    @header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    
    Auth::isAuth();


    $data = Sanitize::jsonValidator(file_get_contents("php://input"));
    $response = array("message" => "Server error");
    $response_code = 200;
    $arr_param = ['title','steps','dates','reward'];

    $validate = !empty($data['id']) && filter_var($data['id'], FILTER_VALIDATE_INT) ? true : false;

    if($validate){

        $DatabaseService = new DatabaseService;
        $connect = $DatabaseService->getConnection();

        try{
            $stmt = $connect->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute(array($data['id']));
        
            $response['message'] = 'Task has been deleted successfully';
        }catch(PDOException $e){
            $response['message'] = 'Server error !';
            $response_code = 400;
        }
    }
    
    echo json_encode($response);
    http_response_code($response_code);
?>