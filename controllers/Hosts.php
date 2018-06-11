<?php
namespace Controller;

use \Firebase\JWT\JWT;
use Phalcon\Mvc\Controller;

class Hosts extends Controller
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

            $robot = $this->request->getJsonRawBody();

						$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
						if(socket_connect($socket, $robot->ip, $robot->port))
						{
              $request = "GET hostsbygroup\nColumns: host_address host_alias groups host_num_services_crit host_num_services_ok host_num_services_unknown host_num_services_warn display_name hard_state\nFilter: groups >= ". $robot->group ."\n";

        			socket_write($socket, $request, strlen($request));
        			socket_shutdown($socket, 1);
        			socket_recv($socket, $buf, 1000000, MSG_WAITALL);
        			socket_close($socket);

              $indexes = explode("\n", $buf);
        			$flex = explode(";", $indexes[0]);

        			$count = 0;

        			foreach ($indexes as $key => $value) {
        				if($value != '') {
        					$exploded_values = explode(";", $value);

        					$array[$count]['address'] = $exploded_values[0];
									$array[$count]['alias'] = $exploded_values[1];
        					$array[$count]['groups'] = $exploded_values[2];
        					$array[$count]['crit'] = $exploded_values[3];
        					$array[$count]['ok'] = $exploded_values[4];
                  $array[$count]['unknown'] = $exploded_values[5];
        					$array[$count]['warn'] = $exploded_values[6];
									$array[$count]['name'] = $exploded_values[7];
									$array[$count]['hard_state'] = $exploded_values[8];

        					$count++;
        				}
        			}

						}
						else {

						}
    				header('Content-type: application/json');
            echo json_encode($array);
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

	public function getByState() {
  	$authHeader = $this->request->getHeader('Authorization');

    if($authHeader) {
    	list($jwt) = sscanf($authHeader, 'Bearer %s');
      if($jwt) {
      	try {
        	$secretKey = base64_decode("8idyoIEFxsf\/DOpNVbhbbxoqdDnda5HH4vDuhZ9Q+1JGYKu0fZaCZZbou1TOPxaKh6ayVx8wAJEs9HynchmVSg==");
					try {
          	$token = JWT::decode($jwt, $secretKey, array('HS512'));
						$phql = "SELECT Model\customer_servers.ip_address, Model\customer_servers.port_number
										 FROM Model\user_hostgroups
										 INNER JOIN Model\customer_servers ON Model\user_hostgroups.customer_server_id = Model\customer_servers.customer_id
										 WHERE Model\user_hostgroups.user_id = " . $token->data->id;
		        $users = $this->modelsManager->executeQuery($phql);
		        $parsed_data = [];
		        $data = [];
						$idx = 0;
						$robot = $this->request->getJsonRawBody();

		        foreach ($users as $user) {
	            $parsed_data[$idx] = [
									'address' => '0.0.0.0',
									'alias' => 'undefined',
									'crit' => 0,
									'ok' => 0,
									'unknown' => 0,
									'warn' => 0,
									'name' => 'undefined'
	            ];

							$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
							if(socket_connect($socket, $user->ip_address, $user->port_number))
							{
								$request = "GET hosts\nColumns: host_address host_alias host_num_services_crit host_num_services_ok host_num_services_unknown host_num_services_warn name\nFilter: state = ". $robot->state ."\n";

								socket_write($socket, $request, strlen($request));
								socket_shutdown($socket, 1);
								socket_recv($socket, $buf, 1000, MSG_WAITALL);
								socket_close($socket);

								$indexes = explode("\n", $buf);
								foreach ($indexes as $key => $value) {
									if($value != '') {
										$exploded_values = explode(";", $value);

										$parsed_data[$idx]['address'] = (isset($exploded_values[0])) ? $exploded_values[0] : '0.0.0.0';
										$parsed_data[$idx]['alias'] = (isset($exploded_values[1])) ? $exploded_values[1] : 'undefined';
										$parsed_data[$idx]['crit'] = (isset($exploded_values[2])) ? $exploded_values[2] : 0;
										$parsed_data[$idx]['ok'] = (isset($exploded_values[3])) ? $exploded_values[3] : 0;
										$parsed_data[$idx]['unknown'] = (isset($exploded_values[4])) ? $exploded_values[4] : 0;
										$parsed_data[$idx]['warn'] = (isset($exploded_values[5])) ? $exploded_values[5] : 0;
										$parsed_data[$idx]['name'] = (isset($exploded_values[6])) ? $exploded_values[6] : 0;

										$idx++;
									}
								}
							}
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
