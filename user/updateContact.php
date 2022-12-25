<?php
    session_start();
    include_once '../include/classes/DatabaseServiceClass.php';
    include_once '../include/classes/SanitizeClass.php';
    include_once '../include/classes/AuthClass.php';
    include_once '../include/classes/ExistsClass.php';
    
    



    @header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");
    
    Auth::isAuth();


    $response = array("message" => "");
    $response_code = 200;


    $data = !empty(file_get_contents("php://input")) ? Sanitize::jsonValidator(file_get_contents("php://input")) : [];

    $arr_param = ['skype', 'telegram', 'phone', 'whatsApp'];

    $validate = false;

    if($_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 4){

        $validate = true;

        foreach($arr_param as $key => $value){

            if(!array_key_exists($value , $data)){
                
                $response = array("message" => "Missing field ". $value);
                $response_code = 400;
                $validate = false;
                break;
            }
        }
    
    }



        $phone = str_replace('+','', $data['phone']);
        $whats = str_replace('+','', $data['whatsApp']);
        $skype = filter_var($data['skype'], FILTER_SANITIZE_STRING);
        $telegram = filter_var($data['telegram'], FILTER_SANITIZE_STRING);



        foreach($data as $key => $value){
            if($value && strlen((string)$value) > 30){
                $validate = false;
            }
        }
        if($phone && !filter_var($phone, FILTER_VALIDATE_INT) || $whats && !filter_var($whats, FILTER_VALIDATE_INT)){
            $validate = false;
        }    



    //
    if($validate){
        
        $DatabaseService = new DatabaseService;
        $connect = $DatabaseService->getConnection();

        $contacts = json_encode($data);

        try{
            $stmt = $connect->prepare("UPDATE users SET contact = ? WHERE user_id = ?");
            $stmt->execute(array($contacts, $_SESSION['id']));

        }catch(PDOException $e){
            echo $e->getMessage();
        }
        

    }


?>