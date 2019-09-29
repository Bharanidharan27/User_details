<?php
    //HEADERS
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    include_once '../../config/Database.php';
    include_once '../../models/User_Details.php';
    include_once '../../models/User.php';

    $database = new Database();
    $db = $database->connect();

    $post = new User_Details($db);
    $user = new User($db);

    $header = getallheaders();
    $auth = $header['Authorization'];
    $auth = str_replace("Bearer ","",$auth);

    $request = $_SERVER["REQUEST_METHOD"];
    $e = $user->validateToken($auth);

    if($e>-1){
        $result = $user->getTokenDetails($e);
        $result = $result->fetch(PDO::FETCH_ASSOC);
        $post->user_id = $result["user_id"];

        if($request == "GET"){
            if(isset($_GET["team_id"])){
                $post->team_id = isset($_GET["team_id"]) ? $_GET["team_id"] : die();
    
                $result = $post->member_details();
    
                $count = $result->rowCount();
    
                if($count > 0){
                    //POST ARRAY
                    $posts_ar = array();
    
                    //ITERATE THROUGH THE FETCHED VALUES
                    while($row  = $result->fetch(PDO::FETCH_ASSOC)){
                        extract($row);
    
                        $post_item = array(
                            'User_ID' => $user_id,
                            'Fullname' => $fullname,
                            'Nationality' => $nationality,
                            'Team_ID' => $team_id,
                            'Profile Picture' => $image,
                            'Role' => $role
                        );
    
                        //PUSH THE VALUES IN '$post_item' to '$posts_arr'
                        array_push($posts_ar, $post_item);
                    }
                    print_r(json_encode($posts_ar));
                }
                else{
                    echo json_encode(array("Error" => "REQUEST_FAILED"));
                    echo json_encode(array("Message" => "NO_TEAM_MEMBERS_EXIST"));
                    echo json_encode(array("Status" => "404"));
                }
                
            }
            else{
                //$post->user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : die();
                
                $result = $post->readSingle();
                $count = $result->rowCount();

                if($count == 1){
                    $row = $result->fetch(PDO::FETCH_ASSOC);
                
                    $post->user_id = $row["user_id"];
                    $post->fullname = $row["fullname"];
                    $post->nationality = $row["nationality"];
                    $post->team_id = $row["team_id"];
                    $post->image = $row['image'];
                    $post->role = $row['role'];
                    $post_arr = array(
                        "User_ID" => $post->user_id,
                        "Fullname" => $post->fullname,
                        "Nationality" => $post->nationality,
                        "Team_ID" => $post->team_id,
                        "Profile Picture" =>$post->image,
                        'Role' => $post->role
                    );
                    print_r(json_encode($post_arr));
                }
                else{
                    echo json_encode(array("Message" => "SERVER_ERROR"));
                    echo json_encode(array("Status" => "504"));
                }
            }
            
        }
    
            elseif($request == "POST"){
                $result = $post->readSingle();
                $row = $result->fetch(PDO::FETCH_ASSOC);
                $post->role = $row["role"];
                if($post->role=="admin"){
                    $data = json_decode(file_get_contents("php://input"));
                    if(isset($data->user_id)&&isset($data->username)&&isset($data->password)&&isset($data->fullname)&&isset($data->nationality)&&isset($data->team_id)&&isset($data->role))
                    {
                        if( filter_var($data->user_id,FILTER_VALIDATE_INT)&&filter_var($data->username,FILTER_SANITIZE_SPECIAL_CHARS)
                        &&filter_var($data->password,FILTER_SANITIZE_SPECIAL_CHARS)&&filter_var($data->fullname,FILTER_SANITIZE_SPECIAL_CHARS)
                        &&filter_var($data->nationality,FILTER_SANITIZE_SPECIAL_CHARS)&&filter_var($data->team_id,FILTER_VALIDATE_INT)
                        &&filter_var($data->role,FILTER_SANITIZE_SPECIAL_CHARS))
                        {
                            $post->user_id = $data->user_id;
                            $post->username = $data->username;
                            $post->password = $user->encrypt($data->password);
                            $post->fullname = $data->fullname;
                            $post->nationality = $data->nationality;
                            $post->team_id = $data->team_id;
                            $post->role = $data->role;
                            print_r(json_encode($post->create_user_and_details()));
                        }
                        else
                        {
                            echo json_encode(array("Error" => "INSERT_FAILED"));
                            echo json_encode(array("Message" => "CHECK_PARAMS"));
                            echo json_encode(array("Status" => "400"));
                        }
                    }  
                    elseif(is_uploaded_file($_FILES['file']['tmp_name']))
                    {
                        $post->user_id = $row["user_id"];
                        $post->change_photo();
                        print_r(json_encode($post->response));
                    }
                    else
                    {
                        echo json_encode(array("Error" => "INSERT_FAILED"));
                        echo json_encode(array("Message" => "MISSING_PARAMS"));
                        echo json_encode(array("Status" => "400"));
                    }
                
                }

                else{
                    $post->user_id = $row["user_id"];
                    $post->change_photo();
                    print_r(json_encode($post->response));
                }                    
            }
            elseif($request == "PUT"){
                $result = $post->readSingle();
                $row = $result->fetch(PDO::FETCH_ASSOC);
                $post->role = $row["role"];
                if($post->role == "admin"){
                    $data = json_decode(file_get_contents("php://input"));
                    if(isset($data->user_id)&&isset($data->fullname)&&isset($data->nationality)&&isset($data->team_id)&&isset($data->role))
                    {
                        if( filter_var($data->user_id,FILTER_VALIDATE_INT)&&filter_var($data->fullname,FILTER_SANITIZE_SPECIAL_CHARS)
                        &&filter_var($data->nationality,FILTER_SANITIZE_SPECIAL_CHARS)&&filter_var($data->team_id,FILTER_VALIDATE_INT)
                        &&filter_var($data->role,FILTER_SANITIZE_SPECIAL_CHARS))
                        {
                            $post->user_id = $data->user_id;
                            $post->fullname = $data->fullname;
                            $post->nationality = $data->nationality;
                            $post->team_id = $data->team_id;
                            $post->role = $data->role;
                            print_r(json_encode($post->update_user_details()));
                        }
                        else
                        {
                            echo json_encode(array("Error" => "UPDATE_FAILED"));
                            echo json_encode(array("Message" => "CHECK_PARAMS"));
                            echo json_encode(array("Status" => "400"));
                        }
                    }
                    else
                    {
                        echo json_encode(array("Error" => "UPDATE_FAILED"));
                        echo json_encode(array("Message" => "MISSING_PARAMS"));
                        echo json_encode(array("Status" => "400"));
                    }
                    
                }
                else{
                    $post->user_id = $row["user_id"];
                    $data = json_decode(file_get_contents("php://input"));
                    $post->pass = $user->encrypt(filter_var($data->pass, FILTER_SANITIZE_SPECIAL_CHARS));
                    $post->retype_pass = $user->encrypt(filter_var($data->retype_pass, FILTER_SANITIZE_SPECIAL_CHARS));
                    $post->change_password();
                    echo json_encode($post->response); 
                }        
               
            }
            elseif($request == "DELETE"){
                $result = $post->readSingle();
                $row = $result->fetch(PDO::FETCH_ASSOC);
                $post->role = $row["role"];
                if($post->role=="admin"){
                    $data = json_decode(file_get_contents("php://input"));
                    if(isset($data->user_id)){
                        if(filter_var($data->user_id, FILTER_VALIDATE_INT)){
                            $post->user_id = $data->user_id;
                            echo json_encode($post->delete_user());
                        }
                        else
                        {
                            echo json_encode(array("Error" => "DELETE_FAILED"));
                            echo json_encode(array("Message" => "CHECK_PARAMS"));
                            echo json_encode(array("Status" => "403"));
                        }
                    }
                    else
                    {
                        echo json_encode(array("Error" => "DELETE_FAILED"));
                        echo json_encode(array("Message" => "MISSING_PARAMS"));
                        echo json_encode(array("Status" => "403"));
                    }    
                }
                else{
                    print_r(json_encode(array("Error" => "DELETE_FAILED")));
                    print_r(son_encode(array("Message" => "ACCESS_DENIED")));
                    print_r(son_encode(array("Status" => "403")));
                }
            }
        
    }
    else{
        switch($e){
            case "-1":
                http_response_code(401);
                echo json_encode(
                    array("error" => "TOKEN_EXPIRED")
                );
                break;
            case "-2":
                http_response_code(401);
                echo json_encode(
                    array("error" => "INVALID_ACCESS_TOKEN")
                );
                break;
        }
    }

    
    

    