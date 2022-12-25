<?php

session_start();


include_once '../../include/classes/DatabaseServiceClass.php';
include_once '../../include/classes/SanitizeClass.php';
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;



@header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");


$validate = false;
$data = Sanitize::jsonValidator(file_get_contents("php://input"));
$response = array("message" => 0);
$response_code = 200;
$arr_param = ['email','password'];


if($_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 2){
    $validate = true;

    foreach($arr_param as $key => $value){
        if(empty($data[$value])     ){
            $response = array("message" => "Missing field ". $value);
            $validate = false;
            break;
        }
    }
}


if($validate){
    
    $email = '';
    $password = '';
    
    $databaseService = new DatabaseService();
    $connect = $databaseService->getConnection();

    $email      =Sanitize::sanitizeFormatEmail(strtolower($data['email']));
    $password   = Sanitize::sanitizeFormatPassword($data['password']);   

    if(!$email){
        $response['message'] = "Error: Email address field";
        $response_code = 400;
        echo json_encode($response);
        return;
    }
    if(!$password){
        $response['message'] = "Error: Password field";
        $response_code = 400;
        echo json_encode($response);
        return;
    }


    $table = 'roles';

    try{
        $stmt = $connect->prepare("SELECT user_id, first_name, last_name, email_address, password FROM ". $table. " WHERE email_address = ? LIMIT 1" );
        $stmt->execute(array($email));
        $count = $stmt->rowCount();

        if($count){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id         = $row['user_id'];
            $firstName  = $row['first_name'];
            $lastName   = $row['last_name'];
            $userPass   = $row['password'];

            if(password_verify($password, $userPass)){
                // Logged in
                $_SESSION['id'] = $id;

                $secret_key         = "v2task";
                $issuer_claim       = "v2task.com";
                $audience_claim     = "api.v2task.com";
                $issuedat_claim     = time();
                $notbefore_claim    = $issuedat_claim + 10; //not before in seconds
                $expire_claim       = $issuedat_claim + 3600; // expire time in seconds

                $token = array(
                    "iss"   => $issuedat_claim,
                    "aud"   => $audience_claim,
                    "iat"   => '1356999524',
                    "nbf"   => '1357000000',
                    "exp"   => $expire_claim,
                    "data"  => array(
                        "id"        => $id,
                        "firstname" => $firstName,
                        "lastname"  => $lastName,
                        "email"     => $email
                    ));
                $response_code = 200;
                $jwt = JWT::encode($token, $secret_key,'HS256');
            
                $response = array(
                    "message"   => "You have successfully logged in!",
                    "jwt"       => $jwt,
                    "user"      => array(
                        "id"        => $id,
                        "firstname" => $firstName,
                        "lastname"  => $lastName,
                        "email"     => $email,
                    )
                );                
            }else{
                $response_code = 400;
                $response['message'] = "Your credentials are incorrect";
            }
        }else{
            $response_code = 400;
            $response['message'] = "Email address not exists";
        }
    }catch(PDOException $e){
        $response_code = 400;
        $response['message'] = "server error !";
    }
}
echo json_encode($response);
http_response_code($response_code);