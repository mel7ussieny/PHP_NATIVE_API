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
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");


    Auth::isAuth();


    $response = array("message" => "");
    $response_code = 200;


    $data = !empty(file_get_contents('php://input', true)) ? Sanitize::jsonValidator(file_get_contents("php://input")) : [];

    $arr_param = ['id', 'status', 'reason'];

    $validate = false;

    if($_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 3 ){
       
        $validate = true;

       
        foreach($arr_param as $key => $value){
            if(!isset($data[$value])){
                $response = array("message" => "Missing field ". $value);
                $response_code = 400;
                $validate = false;
                break;
            }
        }

        
    }

    
    if($validate){

        
        $status = in_array($data['status'], [0,1,2]) ? $data['status'] : 0;
        $id     = filter_var($data['id'], FILTER_VALIDATE_INT) ? $data['id'] : 0;
        $reason = filter_var($data['reason'], FILTER_SANITIZE_STRING);

        $date   = Date("Y-m-d H:i:s");      

        $DatabaseService = new DatabaseService;

        $connect = $DatabaseService->getConnection();


        try{
            $stmt = $connect->prepare("UPDATE user_tasks SET submitted_status = ?, status_updated_at = ?, refused_reason = ? WHERE id = ?");
            $stmt->execute(array($status, $date, $reason ,$id));
    
            if($stmt->rowCount()){
                $response['message'] = "Your update has been saved ";
                $response_code = 200;
            }else{  
                $response['message'] = 'Changes you made may not be saved';
                $response_code = 400;
            }

        }catch(PDOException $e){


            $response['message'] = "Couldn't update your request";
            $response_code = 400;
        }


    }else{
        
        $response_code = 400;
        $response['message'] = 'Bad request !';
        
    }
    
    echo json_encode($response);
    http_response_code($response_code);