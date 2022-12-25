<?php
    include_once '../include/classes/DatabaseServiceClass.php';
    include_once '../include/classes/SanitizeClass.php';
    include_once '../include/classes/ExistsClass.php';
    include_once '../include/MailActivation.php';

    @header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,CLIENT-TYPE");
    

    
    $data = Sanitize::jsonValidator(file_get_contents("php://input"));

    
    $validate = $_SERVER['REQUEST_METHOD'] == 'POST' && count($data) == 1 && !empty($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? true : false;


    $response = array("message" => 0);
    
    if($validate){
    
        $email = $data['email'];

        
        $DatabaseService = new DatabaseService;

        $connect = $DatabaseService->getConnection();


        $stmt = $connect->prepare("SELECT first_name,activation_request FROM users WHERE email_address = ? AND user_activation = 0 AND activation_request < 11 LIMIT 1 ");
        $stmt->execute(array($email));
        

        if($stmt->rowCount()){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $name = $row['first_name'];
            $requests = (INT)$row['activation_request'] + 1 ;
            
            
            $token = null;

            do{
                $token = bin2hex(random_bytes(16));
            }while(Exists::activationExists($token));


            $date = Date('Y-m-d H:i:s');

            try{



                $stmt = $connect->prepare("UPDATE users SET activation_token = ? , user_activation_at =  ?, activation_request = ? WHERE email_address = ?");
                
                $stmt->execute(array($token, $date, $requests,$email));

                sendMail($email, $name, $token);

            }catch(PDOException $e){
                return false;
                // echo $e->getMessage();
            }   
        

        }


    } 



?>