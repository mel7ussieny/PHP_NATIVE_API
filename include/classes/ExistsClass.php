<?php
    include_once 'DatabaseServiceClass.php';
    include_once 'SanitizeClass.php';

    
    class Exists {
        /*
        * check email exists
        *
        * @param $inputEmail
        * @return boolean (True || False)
        */



        public static function emailExists($inputEmail){
            $databaseService = new DatabaseService;
            
            $connect = $databaseService->getConnection();
        
            $stmt = $connect->prepare("SELECT email_address FROM users WHERE email_address = ? LIMIT 1");
            
            $stmt->execute(array($inputEmail));
            
            
            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $stmt->rowCount();
        }

        public static function userIdExists($userId){
            $DatabaseService = new DatabaseService;

            $connect = $DatabaseService->getConnection();

            $stmt = $connect->prepare("SELECT * FROM users WHERE user_id = ?");
            
            $stmt->execute(array($userId));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // $count = $stmt->rowCount();

            return $stmt->rowCount();

        }
        
        public static function rolesEmailExists($inputEmail){
            $databaseService = new DatabaseService;

            $connect = $databaseService->getConnection();

            $stmt = $connect->prepare("SELECT email_address FROM roles WHERE email_address = ? LIMIT 1");

            $stmt->execute(array($inputEmail));
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $stmt->rowCount();
            
        }
        /**
         * Check url token exists
         * @param $inputToken
         * @return boolean(TRUE || FALSE)
         * 
         */
        
        public static function tokenExists($inputToken){
            $databaseService = new DatabaseService;
            
            $connect = $databaseService->getConnection();

            $stmt = $connect->prepare("SELECT url_token from tasks WHERE url_token = ? LIMIT 1");
            
            $stmt->execute(array($inputToken));

            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stmt->rowCount();

        }

        public static function taskExists($inputTask){
            $databaseService = new DatabaseService;

            $connect = $databaseService->getConnection();

            $stmt = $connect->prepare("SELECT id FROM tasks WHERE id = ? LIMIT 1");
            
            $stmt->execute(array($inputTask));

            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stmt->rowCount();
            
        }
        public static function userTaskExists($inputUserTask){
            $databaseService = new DatabaseService;

            $connect = $databaseService->getConnection();

            $stmt = $connect->prepare("SELECT id FROM user_tasks WHERE id = ? LIMIT 1");
            
            $stmt->execute(array($inputUserTask));

            $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stmt->rowCount();
            
        }
        public static function userTaskSubmitted($inputUserId, $inputUserTask){
            $databaseService = new DatabaseService;

            $connect = $databaseService->getConnection();

            $stmt = $connect->prepare("SELECT * FROM user_tasks WHERE user_id = ? AND task_id = ? LIMIT 1");

            $stmt->execute(array($inputUserId, $inputUserTask));
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $stmt->rowCount();
        }

        // public static function isUserActivation($inputUserId){

        //     $DatabaseService = new DatabaseService; 

        //     $connect = $DatabaseService->getConnection();


        //     $stmt = $connect->prepare("SELECT * FROM users WHERE user_id = ? AND user_activation = 1 LIMIT 1");

        //     $stmt->execute(array($inputUserId));

        //     return $stmt->rowCount();
            
        // }

        public static function activationExists($inputToken){
        
            $DatabaseService = new DatabaseService;

            $connect = $DatabaseService->getConnection();


            $stmt = $connect->prepare("SELECT * FROM users WHERE activation_token = ? LIMIT 1");
            

            
            $stmt->execute(array($inputToken));

            return $stmt->rowCount();

        
        }

    }
?>