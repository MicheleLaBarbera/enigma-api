<?php
namespace Controller;

use \Firebase\JWT\JWT;
use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

class Customers extends Controller
{
	public function get() {
  	$authHeader = $this->request->getHeader('Authorization');
    if($authHeader) {
    	list($jwt) = sscanf($authHeader, 'Bearer %s');
      if($jwt) {
      	try {
        	$secretKey = base64_decode("8idyoIEFxsf\/DOpNVbhbbxoqdDnda5HH4vDuhZ9Q+1JGYKu0fZaCZZbou1TOPxaKh6ayVx8wAJEs9HynchmVSg==");
					try {
          	$token = JWT::decode($jwt, $secretKey, array('HS512'));

            $phql = 'SELECT id, name, logo FROM Model\customers ORDER BY name';
						$customers = $this->modelsManager->executeQuery($phql);

						$data = [];

						foreach ($customers as $customer) {
							$data[] = [
								'id'   => $customer->id,
								'name' => $customer->name,
								'logo' => $customer->logo
							];
						}

    				header('Content-type: application/json');
            echo json_encode($data);
          } catch(\Firebase\JWT\ExpiredException $e) {
          	echo json_encode([
							'error' => $e->getMessage()
						]);
          }
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
  }

	public function getID($id) {
  	$authHeader = $this->request->getHeader('Authorization');
    if($authHeader) {
    	list($jwt) = sscanf($authHeader, 'Bearer %s');
      if($jwt) {
      	try {
        	$secretKey = base64_decode("8idyoIEFxsf\/DOpNVbhbbxoqdDnda5HH4vDuhZ9Q+1JGYKu0fZaCZZbou1TOPxaKh6ayVx8wAJEs9HynchmVSg==");
					try {
          	$token = JWT::decode($jwt, $secretKey, array('HS512'));

            $phql = 'SELECT id, description, ip_address, port_number FROM Model\customer_servers WHERE customer_id = '. $id .'ORDER BY description';
						$customer_servers = $this->modelsManager->executeQuery($phql);

						$data = [];

						foreach ($customer_servers as $customer_server) {
							$data[] = [
								'id'   => $customer_server->id,
								'description' => $customer_server->description,
								'ip_address' => $customer_server->ip_address,
								'port_number' => $customer_server->port_number
							];
						}

    				header('Content-type: application/json');
            echo json_encode($data);
          } catch(\Firebase\JWT\ExpiredException $e) {
          	echo json_encode([
							'error' => $e->getMessage()
						]);
          }
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
  }
	public function create() {
		$customer = $this->request->getJsonRawBody();
		$phql = 'INSERT INTO Model\customers (name, logo) VALUES (:name:, :logo:)';
		//print_r($customer->logo);
		$status = $this->modelsManager->executeQuery(
			$phql,
			[
				'name' => $customer->name,
				'logo' => $customer->logo
			]
		);

		// Create a response
		$response = new Response();

		// Check if the insertion was successful
		if ($status->success() === true) {
			$response->setJsonContent([
				'status' => 201,
				'body'   => [
					'message' => 'Cliente registrato con successo.'
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
		return $response;
 	}
}
?>
