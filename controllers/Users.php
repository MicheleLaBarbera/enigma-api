<?php
namespace Controller;

use \Firebase\JWT\JWT;
use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

class Users extends Controller
{
	public function auth() {
        $robot = $this->request->getJsonRawBody();
	
				$phql = "SELECT Model\Users.id, Model\Users.password, Model\Users.firstname, Model\Users.lastname, Model\customers.logo FROM Model\customer_users INNER JOIN Model\customers ON customer_id = Model\customers.id INNER JOIN Model\Users ON user_id = Model\Users.id WHERE Model\Users.username = '". $robot->username ."' LIMIT 1";

        $users = $this->modelsManager->executeQuery($phql);
        $parsed_data = [];
        $data = [];

        foreach ($users as $user) {
            $parsed_data[] = [
                'id'   => $user->id,
                'password' => $user->password,
								'firstname' => $user->firstname,
								'lastname' => $user->lastname,
								'logo' => $user->logo
            ];
        }

        if(isset($parsed_data[0]['password'])) {
	        if(password_verify($robot->password, $parsed_data[0]['password'])) {
	        	$tokenId    = base64_encode(mcrypt_create_iv(32));
                $issuedAt   = time();
                $notBefore  = $issuedAt + 10;
                $expire     = $notBefore + 1 * 60 * 60;
                $serverName = "AngularCMK";

                $data = [
                    'iat'  => $issuedAt,
                    'jti'  => $tokenId,
                    'iss'  => $serverName,
                    'nbf'  => $notBefore,
                    'exp'  => $expire,
                    'data' => [
                        'id'   => $parsed_data[0]['id'],
												'firstname' => $parsed_data[0]['firstname'],
												'lastname' => $parsed_data[0]['lastname']
                    ]
                ];
                $secretKey = base64_decode("8idyoIEFxsf\/DOpNVbhbbxoqdDnda5HH4vDuhZ9Q+1JGYKu0fZaCZZbou1TOPxaKh6ayVx8wAJEs9HynchmVSg==");
                $jwt = JWT::encode($data, $secretKey, 'HS512');

								$response["status"] = 200;
								$response["body"] = new Requests();
                $response["body"]->token = $jwt;
								$response["body"]->logo = base64_encode($parsed_data[0]['logo']);
								echo json_encode($response);

	        }
	        else
	    		echo json_encode("");
	    }
	    else
	    	echo json_encode("");
    }

		public function create() {
			$user = $this->request->getJsonRawBody();
			$phql = 'INSERT INTO Model\Users (firstname, lastname, username, password) VALUES (:firstname:, :lastname:, :username:, :password:)';

			$status = $this->modelsManager->executeQuery(
				$phql,
				[
					'firstname' => $user->firstname,
					'lastname' => $user->lastname,
					'username' => $user->username,
					'password' => password_hash($user->password, PASSWORD_BCRYPT)
				]
			);

			// Create a response
			$response = new Response();

			// Check if the insertion was successful
			if ($status->success() === true) {
				$user_id = $status->getModel()->id;

				$phql = 'INSERT INTO Model\customer_users (customer_id, user_id) VALUES (:customer_id:, :user_id:)';

				$status = $this->modelsManager->executeQuery(
					$phql,
					[
						'customer_id' => $user->customer,
						'user_id' => $user_id
					]
				);
				if ($status->success() === true) {
					$response->setJsonContent([
						'status' => 201,
					  'body'   => [
						 	'message' => 'Utente registrato con successo.'
						]
					]);
				}
				else {
					// Send errors to the client
					$errors = [];

					foreach ($status->getMessages() as $message) {
						$errors[] = $message->getMessage();
					}

					$response->setJsonContent(
					[
						'status' => '409',
						'body'   => [
							'message' => $errors,
						]
					]);
				}
			}
			else {
				// Send errors to the client
				$errors = [];

				foreach ($status->getMessages() as $message) {
					$errors[] = $message->getMessage();
				}

				$response->setJsonContent(
				[
					'status' => '409',
					'body'   => [
						'message' => $errors,
					]
				]);
			}
		 	return $response;
	 }

	 public function get() {
		 $phql = 'SELECT * FROM Model\Users ORDER BY firstname, lastname, username';
	 }
}

?>
