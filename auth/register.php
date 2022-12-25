<?php
session_start();
    include_once '../include/classes/DatabaseServiceClass.php';
    include_once '../include/classes/ExistsClass.php';
    include_once '../include/classes/SanitizeClass.php';
    include_once '../include/MailActivation.php';


    @header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");

    
    $validate = false;
    $data = Sanitize::jsonValidator(file_get_contents("php://input"));
    $response = array("message" => 'Server error');
    $response_code = 200;
    $arr_param = ['first_name','last_name','email','password','country'];
    


    
    if($_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 5){
        $validate = true;
        
        foreach($arr_param as $key => $value){
            if(empty($data[$value])){
                $response = array("message" => "Missing field ". $value);
                $validate = false;
                break;
            }
            if(in_array($value,$data)){
                    $validate = false;
                    break;
            }
        }
    }
    


    if($validate && !isset($_SESSION['id'])){
        
        $first_name = '';
        $last_name = '';
        $email = '';
        $password = '';
        $connect = null;
    
        $databaseService = new DatabaseService();
        $connect = $databaseService->getConnection();
        

        $first_name  = Sanitize::sanitizeFormatText($data['first_name']);
        $last_name   = Sanitize::sanitizeFormatText($data['last_name']);
        $email      = Sanitize::sanitizeFormatEmail(strtolower($data['email']));
        $password   = Sanitize::sanitizeFormatPassword($data['password']);
        $country    = Sanitize::sanitizeFormatText($data['country']);
        $updated    = 0;
        $userActv   = 0;
        $userActvDate = null;


        /**
         * Check Email Exists 
         * 
         * @param email address
         * 
         * @return 400 and message if exists
         * 
        */
        if(Exists::emailExists($email)){
            $response['message'] = "dublicated entry of email address";
            $response_code = 400;
        }
        
        if(!$first_name){
            $response['message'] = "Error: First name field";
            $response_code = 400;
        }

        if(!$last_name){
            $response['message'] = "Error: Last name field";
            $response_code = 400;
        }

        if(!$email){
            $response['message'] = "Error: Email address field";
            $response_code = 400;
        }

        if(!$password){
            $response['message'] = "Error: Password field";
            $response_code = 400;
        }

        if(!$country){
            $response['message'] = "Error: Country field";
            $response_code = 400;
        }
        

        /**
         * Add User After Passing All Validations
         */
        $table_name = 'users';
        $password_hash = password_hash($password,PASSWORD_BCRYPT);
        
        if($response_code == 200){
            try{

                $token = null;

                do{
                    $token = bin2hex(random_bytes(16));
                }while(Exists::activationExists($token));


                $stmt = $connect->prepare("INSERT INTO " . $table_name."(first_name, last_name, email_address, password, country, updated_at, activation_token ,user_activation, user_activation_at,activation_request) VALUES (:first, :last, :email, :pass, :country, :update, :token, :user_act, :user_act_at, :act_req)");
                $stmt->execute(array(
                    "first"     => $first_name,
                    "last"      => $last_name,
                    "email"     => $email,
                    "pass"      => $password_hash,
                    "country"   => $country,
                    "update"    => 0,
                    "user_act"  => 0,
                    "user_act_at"   => date('Y-m-d H:i:s'),
                    "token"  => $token,
                    "act_req" => 1
                ));

                sendMail($email, $first_name, $token);

                $response['message'] = "User was successfully registered.";
                $response_code = 201;
    
            }catch(PDOException $e){
                // echo $e->getMessage();
                $response_code = 400;
                $response['message'] = "unable to register the user.";
            }
        }
    }
    echo json_encode($response);
    http_response_code($response_code);

?>