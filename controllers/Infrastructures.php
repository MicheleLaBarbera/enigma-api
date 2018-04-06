<?php

namespace Controller;

use Phalcon\Mvc\Controller;

class Infrastructures extends Controller
{
	public function get() {
        //$robot = $this->request->getJsonRawBody();
        $authHeader = $this->request->getHeader('Authorization');

        if ($authHeader) {

            list($jwt) = sscanf( $authHeader, 'Bearer %s');
     
            if ($jwt) {
                try {
                   
                    $secretKey = base64_decode("8idyoIEFxsf\/DOpNVbhbbxoqdDnda5HH4vDuhZ9Q+1JGYKu0fZaCZZbou1TOPxaKh6ayVx8wAJEs9HynchmVSg==");
                    
                    $token = JWT::decode($jwt, $secretKey, array('HS512'));

                   
                    /*
                     * return protected asset
                     */
                    header('Content-type: application/json');
                    echo json_encode([
                        'msg'    => "Benvenuto"
                    ]);

                } catch (Exception $e) {
                    header('HTTP/1.0 401 Unauthorized');
                }
            }
            else {
               header('HTTP/1.0 400 Bad Request');
            }
        }
        else {        
            header('HTTP/1.0 400 Bad Request');
            echo 'Token not found in request';
        }
        
        /*$phql = "SELECT id, password FROM Model\Users WHERE username = '". $robot->username ."' LIMIT 1";
        $users = $this->modelsManager->executeQuery($phql);
        $parsed_data = [];
        $data = [];

        foreach ($users as $user) {
            $parsed_data[] = [
                'id'   => $user->id,
                'password' => $user->password
            ];
        }

        if(isset($parsed_data[0]['password'])) {
	        if(password_verify($robot->password, $parsed_data[0]['password']))
	        {
	        	$tokenId    = base64_encode(mcrypt_create_iv(32));
                $issuedAt   = time();
                $notBefore  = $issuedAt + 10;             
                $expire     = $notBefore + 60;           
                $serverName = "AngularCMK";

                $data = [
                    'iat'  => $issuedAt,         
                    'jti'  => $tokenId,          
                    'iss'  => $serverName,      
                    'nbf'  => $notBefore,       
                    'exp'  => $expire,           
                    'data' => [                 
                        'id'   => $parsed_data[0]['id']             
                    ]
                ]; 
                $secretKey = base64_decode("8idyoIEFxsf\/DOpNVbhbbxoqdDnda5HH4vDuhZ9Q+1JGYKu0fZaCZZbou1TOPxaKh6ayVx8wAJEs9HynchmVSg==");
                $jwt = JWT::encode($data, $secretKey, 'HS512');
                
                $unencodedArray = ['token' => $jwt];
                echo json_encode($unencodedArray);                  
	        } 
	        else	    
	    		echo json_encode("");      
	    }  
	    else	    
	    	echo json_encode("");     */ 
    }
    
}


?>