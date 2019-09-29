<?php 
  // Headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

  $request = $_SERVER["REQUEST_METHOD"];
  
  if($request=="OPTIONS")
  {
    http_response_code(200);
    echo json_encode(
      array('Allow:' => "POST",
            'Content-Type' => "application/json")
    );
  }
  elseif($request=="POST")
  {
      include_once '../../../config/Database.php';
      include_once '../../../models/User.php';
    
      // Instantiate DB & connect
      $database = new Database();
      $db = $database->connect();
    
      //New user object
      $user = new User($db);  

      $data = json_decode(file_get_contents("php://input"));

      if(isset($data->username) && isset($data->password))
      {
          if(filter_var($data->username,FILTER_VALIDATE_EMAIL))
          {
              $pass = $user->encrypt($data->password);
              $result = $user->readSingle($data->username);
              $final = $result->fetch(PDO::FETCH_ASSOC);
      
              $num = $result->rowCount();
      
              if($num!=0) 
              {
                    $username = $final['username'];
                    if($pass==$final['password'])
                    {   
                      $token = $user->validateToken($final['user_id']);
                      if($token=="-1" || $token=="-2")
                      { 
                        $token = $user->createToken($final["user_id"]);
                      }
      
                      echo json_encode(
                        array('message' => 'Success',
                              'token' => $token)
                      );
                    } 
                    else 
                    {   http_response_code(406);
                        echo json_encode(
                        array('error' => 'Invalid Password')
                      );
                    }
              }
              else
              {       
                      http_response_code(404);
                      echo json_encode(
                        array('error' => 'User not found')
                      );
              }
          }
          else
          {
              http_response_code(406);
              echo json_encode(
                array('error' => 'Invalid Email')
              );
          }
      }
      else
      {       http_response_code(406);    
              echo json_encode(
                array('error'=> 'Fields cannot be empty!')
              );
      }
  }

?>