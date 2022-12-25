<?php

    class DatabaseService{
        private $db_host = "localhost";
        private $db_name = "v2task";
        private $db_user = "root";
        private $db_pass = "";

        private $connection;


        public function getConnection(){
            $this->connection = null;

            try{
                $this->connection = new PDO("mysql:host=".$this->db_host.";dbname=".$this->db_name,$this->db_user, $this->db_pass,   array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ));
            }catch(PDOException $e){
                echo "Connection failed: " . $e->getMessage();
            }
            return $this->connection;
        }
    }

?>