<?php

namespace Controller;

use \Firebase\JWT\JWT;
use Phalcon\Mvc\Controller;

class Users extends Controller
{
	public function auth() {
        $robot = $this->request->getJsonRawBody();

        $phql = "SELECT id, password FROM Model\Users WHERE username = '". $robot->username ."' LIMIT 1";
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
	        if(password_verify($robot->password, $parsed_data[0]['password'])) {
	        	$tokenId    = base64_encode(mcrypt_create_iv(32));
                $issuedAt   = time();
                $notBefore  = $issuedAt + 10;             
                $expire     = $notBefore + 3600000;           
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
	    	echo json_encode("");      
    }    
}

?>