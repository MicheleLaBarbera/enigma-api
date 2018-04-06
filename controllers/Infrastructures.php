<?php
namespace Controller;

use \Firebase\JWT\JWT;
use Phalcon\Mvc\Controller;

class Infrastructures extends Controller
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
						$phql = "SELECT Model\customer_servers.id, Model\customers.name, Model\customer_servers.ip_address, Model\customer_servers.port_number, Model\customer_servers.description
										 FROM Model\user_infrastructures
										 INNER JOIN Model\customer_servers ON Model\user_infrastructures.customer_server_id = Model\customer_servers.customer_id
										 INNER JOIN Model\customers ON Model\customer_servers.customer_id = Model\customers.id
										 WHERE Model\user_infrastructures.user_id = " . $token->data->id;
		        $users = $this->modelsManager->executeQuery($phql);
		        $parsed_data = [];
		        $data = [];
						$idx = 0;

		        foreach ($users as $user) {
		            $parsed_data[$idx] = [
		                'id'   => $user->id,
										'name' => $user->name,
										'ip' => $user->ip_address,
  									'port' => $user->port_number,
  									'state' => 'active',
  									'description' => $user->description,
  									'status' => 'up'
		            ];

								$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
								if(socket_connect($socket, $parsed_data[$idx]['ip'], $parsed_data[$idx]['port']))
								{
									$request = "GET hostgroups\nColumns: alias worst_service_state\n";

									socket_write($socket, $request, strlen($request));
									socket_shutdown($socket, 1);
									socket_recv($socket, $buf, 1000, MSG_WAITALL);
									socket_close($socket);

									$indexes = explode("\n", $buf);
									$flex = explode(";", $indexes[0]);

									$count = 0;

									foreach ($indexes as $key => $value) {
										if($value != '') {
											$exploded_values = explode(";", $value);


											$exploded_values_ex = explode("-", $exploded_values[0]);
											$parsed_data[$idx]['hostgroups'][$count]['alias'] = $exploded_values_ex[1];
											$parsed_data[$idx]['hostgroups'][$count]['status'] = $exploded_values[1];

											if($parsed_data[$idx]['hostgroups'][$count]['status'] == 1 && $parsed_data[$idx]['status'] == 'up')
												$parsed_data[$idx]['status'] = 'warning';

											if($parsed_data[$idx]['hostgroups'][$count]['status'] == 2 && ($parsed_data[$idx]['status'] == 'up' || $parsed_data[$idx]['status'] == 'warning'))
												$parsed_data[$idx]['status'] = 'critical';

											$count++;
										}
									}
								}
								else {
									$parsed_data[$idx]['up'] = 0;
									$parsed_data[$idx]['warning'] = 0;
									$parsed_data[$idx]['critical'] = 0;
						    	$parsed_data[$idx]['status'] = 'offline';
								}
								$idx++;
		        }
						header('Content-type: application/json');
            echo json_encode($parsed_data);
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
