<?php
    class Database{
        private $host = "localhost";
        private $db_name = "pro";
        private $user = "root";
        private $password = "rb";
        private $conn;

        public function connect(){
            $this->conn = null;
      
            try{
                //Syntax to create PDO object
                #PROPERTY = new PDO('database type:host ='. HOST PROPERTY. ';dbname=' .DB NAME 
                #                     , USER PROPERTY, USER PASSWORD, OPTIONS);
                $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->user, $this->password,array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
                 //GET ERROR MODE
                 //"setAttribute" CAN BE USED TO SET ERRORMODE, WE CAN SET THE ERRORMODES WE WANT TO SEE.
                $this->conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $e){
                echo "Error : ".$e->getMessage();
            }

            return $this->conn;
        }

    }
?>