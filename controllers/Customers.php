<?php
namespace Controller;

use \Firebase\JWT\JWT;
use Phalcon\Mvc\Controller;

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

            $phql = 'SELECT id, name, logo FROM Model\customers';
						$customers = $this->modelsManager->executeQuery($phql);

						$data = [];

						foreach ($customers as $customer) {
							$data[] = [
								'id'   => $customer->id,
								'name' => $customer->name,
								'logo' => base64_encode($customer->logo)
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
}
?>
