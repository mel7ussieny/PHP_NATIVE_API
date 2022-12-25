<?php


    class Auth{

        public static function isAuth(){        
            if(empty($_SESSION['id'])){    
                header('HTTP/1.1 401 User is unauthorized');
                exit;
           }
        }
    }

?>