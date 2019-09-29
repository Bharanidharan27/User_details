<?php
    class User_Details{
        private $conn;

        public $username;
        public $password;
        public $user_id;
        public $fullname;
        public $nationality;
        public $team_id;
        public $image;
        public $role;
        public $language;
        public $pass;
        public $retype_pass;
        public $connection;
        public $response = array();

        public function __construct($db){
            $this->conn = $db;
            $this->connection=$this->conn;
        }

        public function create_user_and_details(){
            try{
                $this->conn = $this->connection;
                $query1= "INSERT INTO users SET user_id=:user_id, username = :username, password = :password";
                $stmt = $this->conn->prepare($query1);
                $stmt->bindParam(':user_id', $this->user_id);
                $stmt->bindParam(":username", $this->username);
                $stmt->bindParam(":password", $this->password);
                if($stmt->execute()){
                    // CONTINUE
                }else{
                    $result["Error"] = "INSERT_FAILED";
                    $result["Message"] = "CHECK_PARAMS";
                    $result["Status"] = "400";
                    return $result;
                }

                $query2 = 'INSERT INTO user_details SET user_id = :user_id, fullname = :fullname, 
                nationality = :nationality, team_id = :team_id, role = :role';
                $stmt= $this->conn->prepare($query2);
                
                $stmt->bindParam(':user_id', $this->user_id);
                $stmt->bindParam(':fullname', $this->fullname);
                $stmt->bindParam(':nationality', $this->nationality);
                $stmt->bindParam(':team_id', $this->team_id);
                $stmt->bindParam(':role', $this->role);
                
                if($stmt->execute()){
                    $result['Message'] = 'INSERT_SUCCESS';
                    $result["User Name"] = $this->username;
                    $result["Full Name"] = $this->fullname;
                    $result["Nationality"] = $this->nationality;
                    $result["Team ID"] = $this->team_id;
                    $result["Role"] = $this->role;
                    $result['Status'] = '200';
                }
                else{
                    $result['message'] = 'SERVER_ERROR';
                    $result['Status'] = '504';
                }
                $this->conn = null;
                return $result;
            }
            catch(Exception $e){
                echo json_encode($e->getMessage());
            }
            
        }

        public function update_user_details(){
            try{
                $this->conn = $this->connection;
                $query = 'UPDATE user_details SET user_id = :user_id, fullname = :fullname,
                nationality = :nationality, team_id = :team_id, role = :role WHERE user_id = :user_id';
                $stmt= $this->conn->prepare($query);
                
                $stmt->bindParam(':user_id', $this->user_id);
                $stmt->bindParam(':fullname', $this->fullname);
                $stmt->bindParam(':nationality', $this->nationality);
                $stmt->bindParam(':team_id', $this->team_id);
                $stmt->bindParam(':role', $this->role);
                
                if($stmt->execute()){
                    $result['message'] = 'UPDATE_SUCCESS';
                    $result['Status'] = '204';
                }
                else{
                    $result['message'] = 'SERVER_ERROR';
                    $result['Status'] = '500';
                }
                $this->conn = null;
                return $result;
            }
            catch(Exception $e){
                echo json_encode($e->getMessage());
            }
        }

        public function delete_user(){
            $this->conn = $this->connection;
            try{
                // try
                // {
                //     $query3="DELETE FROM sessions WHERE user_id = :user_id";
                //     $stmt = $this->conn->prepare($query3);
                //     $stmt->bindParam(":user_id", $this->user_id);
                //     $stmt->execute();
                // }
                // catch(Exception $e)
                // {
                //     echo "NO_ACTIVE_SESSIONS";
                // }

                $query ="UPDATE users SET active = 0 WHERE user_id =:user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $this->user_id);
                
                if($stmt->execute()){
                    $result['Message'] = 'USER_DELETED';
                    $result['Status'] = '200';   
                }
                else{
                    $result['message'] = 'SERVER_ERROR';
                    $result['Status'] = '504';
                }
                $this->conn = null;
                return $result; 
            }
            catch(Exception $e){
                echo json_encode($e->getMessage());
            }
            
        }

        public function readSingle(){
            $this->conn = $this->connection;
            try{
                $query = "SELECT * FROM user_details WHERE user_id = '.$this->user_id.'";

                $stmt = $this->conn->prepare($query);

                $stmt->execute();

                $this->conn = null;
                return $stmt;
            }
            catch(Exception $e)
            {
                echo json_encode($e->getMessage());
            }
        }

        public function member_details(){
            try{
                $this->conn = $this->connection;
                $query = "SELECT user_id,fullname,nationality,team_id,image,role FROM user_details WHERE team_id = ? and user_id <> $this->user_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(1, $this->team_id);
                $stmt->execute();

                $this->conn = null;
                return $stmt;
            }
            catch(Exception $e){
                echo json_encode($e->getMessage());
            }
        }

        public function change_photo(){
            try{
                $this->conn = $this->connection;
        
                if(is_uploaded_file($_FILES['file']['tmp_name'])){
                    $tmpname = $_FILES['file']['tmp_name'];
                    $img_name = $_FILES['file']['name'];
                    $img_extn = '.'.pathinfo($img_name, PATHINFO_EXTENSION);
                    // SET THE DIRECTORY WHERE YOU WANT TO MOVE THE FILE WITH THE NAME IN WHICH\
                    // YOU WANT TO SAVE
                    $hashed_img = md5($img_name).$img_extn;
                    $dir = 'images/'. $hashed_img;
                    //$tmpname = file_get_contents($tmpname);
            
                    if(move_uploaded_file($tmpname,$dir)){
                        //execute this query in here and if this is a success. then return a path in response 
                        //http://localhost/projectmanagementtool/projectmanagementtool/api/user_details/ . $dir.
                        $path = "http://localhost/projectmanagementtool/projectmanagementtool/api/user_details/$dir";
                        $query = "UPDATE user_details SET image = '.$img_name.' WHERE user_id = ".$this->user_id;        
                        $stmt = $this->conn->prepare($query);
                        if($stmt->execute()){
                            $this->response["Message"] ="UPLOADED_SUCCESSFULLY";
                            //$this->response['location'] = $dir;
                            $this->response["Status"] = "200";
                            $this->response["Path"] = $path;
                        }
                    }
                    else{
                        $this->response["Error"] = "SERVER_ERROR";
                        // $this->response['location'] = $img_extn;
                        $this->response["Status"] = "400";
                    }
                
                }
                else{
                    $this->response["Error"] = "UPLOAD_FAILED";
                    $this->response["Message"] ="MISSING_PARAMS";
                    $this->response["Status"] = "404";
                }
                $this->conn = null;
                return $this->response;
            }
            catch(Exception $e){
                echo json_encode($e->getMessage());
            }
            
        }

        public function change_password(){
            try{
                $this->conn = $this->connection;
                if($this->pass == $this->retype_pass){
                    $query = "UPDATE users SET password = '$this->pass' WHERE user_id = ?";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(1, $this->user_id);
                    
                    if($stmt->execute()){
                        $this->response["Message"] ="UPDATE_SUCCESS";
                        $this->response["Status"] = "200";
                    }
                    else{
                        $this->response["Error"] ="SERVER_ERROR";
                        $this->response["Status"] = "504";
                    }
                }
                else{
                    $this->response["Error"] = "UPDATE_FAILED";
                    $this->response["Message"] = "CHECK_PARAMS";
                    $this->response["Status"] = "404";
                }
                $this->conn = null;
                return $this->response;
            }
            catch(Exception $e){
                echo json_encode($e->getMessage());
            }
        }
    }

?>