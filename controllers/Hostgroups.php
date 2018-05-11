<?php
namespace Controller;

use \Firebase\JWT\JWT;
use Phalcon\Mvc\Controller;

class Hostgroups extends Controller
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
						$phql = "SELECT Model\user_hostgroups.state, Model\customer_servers.id, Model\customers.name, Model\customer_servers.ip_address, Model\customer_servers.port_number, Model\customer_servers.description
										 FROM Model\user_hostgroups
										 INNER JOIN Model\customer_servers ON Model\user_hostgroups.customer_server_id = Model\customer_servers.customer_id
										 INNER JOIN Model\customers ON Model\customer_servers.customer_id = Model\customers.id
										 WHERE Model\user_hostgroups.user_id = " . $token->data->id;
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
  									'state' => $user->state,
  									'description' => $user->description,
  									'status' => 'up',
										'hosts_down' => 0,
										'hosts_pending' => 0,
										'hosts_unreachable' => 0,
										'hosts_up' => 0,
										'services_crit' => 0,
										'services_ok' => 0,
										'services_pending' => 0,
										'services_unknown' => 0,
										'services_warn' => 0
		            ];

								$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
								if(socket_connect($socket, $parsed_data[$idx]['ip'], $parsed_data[$idx]['port']))
								{
									$request = "GET hostgroups\nColumns: alias worst_service_state name num_hosts_down num_hosts_pending num_hosts_unreach num_hosts_up num_services_crit num_services_ok num_services_pending num_services_unknown num_services_warn\n";

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
											$parsed_data[$idx]['groups'][$count]['alias'] = $exploded_values_ex[1];
											$parsed_data[$idx]['groups'][$count]['status'] = $exploded_values[1];
											$parsed_data[$idx]['groups'][$count]['name'] = $exploded_values[2];

											if($parsed_data[$idx]['groups'][$count]['status'] == 1 && $parsed_data[$idx]['status'] == 'up')
												$parsed_data[$idx]['status'] = 'warning';
											else if($parsed_data[$idx]['groups'][$count]['status'] == 2 && ($parsed_data[$idx]['status'] == 'up' || $parsed_data[$idx]['status'] == 'warning'))
												$parsed_data[$idx]['status'] = 'critical';
											else if($parsed_data[$idx]['groups'][$count]['status'] == 3 && $parsed_data[$idx]['status'] == 'critical')
												$parsed_data[$idx]['status'] = 'critical';
											else if($parsed_data[$idx]['groups'][$count]['status'] == 3 && $parsed_data[$idx]['status'] != 'up')
													$parsed_data[$idx]['status'] = 'warning';

											$parsed_data[$idx]['hosts_down'] += $exploded_values[3];
											$parsed_data[$idx]['hosts_pending'] += $exploded_values[4];
											$parsed_data[$idx]['hosts_unreachable'] += $exploded_values[5];
											$parsed_data[$idx]['hosts_up'] += $exploded_values[6];

											$parsed_data[$idx]['services_crit'] += $exploded_values[7];
											$parsed_data[$idx]['services_ok'] += $exploded_values[8];
											$parsed_data[$idx]['services_pending'] += $exploded_values[9];
											$parsed_data[$idx]['services_unknown'] += $exploded_values[10];
											$parsed_data[$idx]['services_warn'] += $exploded_values[11];

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

	public function getID($id) {
  	$authHeader = $this->request->getHeader('Authorization');

    if($authHeader) {
    	list($jwt) = sscanf($authHeader, 'Bearer %s');
      if($jwt) {
      	try {
        	$secretKey = base64_decode("8idyoIEFxsf\/DOpNVbhbbxoqdDnda5HH4vDuhZ9Q+1JGYKu0fZaCZZbou1TOPxaKh6ayVx8wAJEs9HynchmVSg==");
					try {
          	$token = JWT::decode($jwt, $secretKey, array('HS512'));
						$phql = "SELECT Model\user_hostgroups.default_group, Model\user_hostgroups.state, Model\customer_servers.id, Model\customers.name, Model\customer_servers.ip_address, Model\customer_servers.port_number, Model\customer_servers.description
										 FROM Model\user_hostgroups
										 INNER JOIN Model\customer_servers ON Model\user_hostgroups.customer_server_id = Model\customer_servers.customer_id
										 INNER JOIN Model\customers ON Model\customer_servers.customer_id = Model\customers.id
										 WHERE Model\user_hostgroups.user_id = ". $token->data->id . " AND Model\user_hostgroups.customer_server_id = ". $id;
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
  									'state' => $user->state,
  									'description' => $user->description,
										'default_group' => $user->default_group,
  									'status' => 'up',
										'hosts_down' => 0,
										'hosts_pending' => 0,
										'hosts_unreachable' => 0,
										'hosts_up' => 0,
										'services_crit' => 0,
										'services_ok' => 0,
										'services_pending' => 0,
										'services_unknown' => 0,
										'services_warn' => 0
		            ];

								$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
								if(socket_connect($socket, $parsed_data[$idx]['ip'], $parsed_data[$idx]['port']))
								{
									$request = "GET hostgroups\nColumns: alias worst_service_state name num_hosts_down num_hosts_pending num_hosts_unreach num_hosts_up num_services_crit num_services_ok num_services_pending num_services_unknown num_services_warn\n";

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
											$parsed_data[$idx]['groups'][$count]['alias'] = $exploded_values_ex[1];
											$parsed_data[$idx]['groups'][$count]['status'] = $exploded_values[1];
											$parsed_data[$idx]['groups'][$count]['name'] = $exploded_values[2];
											$parsed_data[$idx]['groups'][$count]['state'] = 'inactive';
											$parsed_data[$idx]['groups'][$count]['count'] = $count;

											if($parsed_data[$idx]['groups'][$count]['status'] == 1 && $parsed_data[$idx]['status'] == 'up')
												$parsed_data[$idx]['status'] = 'warning';
											else if($parsed_data[$idx]['groups'][$count]['status'] == 2 && ($parsed_data[$idx]['status'] == 'up' || $parsed_data[$idx]['status'] == 'warning'))
												$parsed_data[$idx]['status'] = 'critical';
											else if($parsed_data[$idx]['groups'][$count]['status'] == 3 && $parsed_data[$idx]['status'] == 'critical')
												$parsed_data[$idx]['status'] = 'critical';
											else if($parsed_data[$idx]['groups'][$count]['status'] == 3 && $parsed_data[$idx]['status'] != 'up')
													$parsed_data[$idx]['status'] = 'warning';

											$parsed_data[$idx]['hosts_down'] += $exploded_values[3];
											$parsed_data[$idx]['hosts_pending'] += $exploded_values[4];
											$parsed_data[$idx]['hosts_unreachable'] += $exploded_values[5];
											$parsed_data[$idx]['hosts_up'] += $exploded_values[6];

											$parsed_data[$idx]['services_crit'] += $exploded_values[7];
											$parsed_data[$idx]['services_ok'] += $exploded_values[8];
											$parsed_data[$idx]['services_pending'] += $exploded_values[9];
											$parsed_data[$idx]['services_unknown'] += $exploded_values[10];
											$parsed_data[$idx]['services_warn'] += $exploded_values[11];

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

	public function setDefaultGroup($id) {
  	$authHeader = $this->request->getHeader('Authorization');

    if($authHeader) {
    	list($jwt) = sscanf($authHeader, 'Bearer %s');
      if($jwt) {
      	try {
        	$secretKey = base64_decode("8idyoIEFxsf\/DOpNVbhbbxoqdDnda5HH4vDuhZ9Q+1JGYKu0fZaCZZbou1TOPxaKh6ayVx8wAJEs9HynchmVSg==");
					try {
						$token = JWT::decode($jwt, $secretKey, array('HS512'));
						$robot = $this->request->getJsonRawBody();
						//print_r($robot);

						$phql = "UPDATE Model\user_hostgroups SET Model\user_hostgroups.default_group = '". $robot->value ."' WHERE Model\user_hostgroups.user_id = ". $token->data->id . " AND Model\user_hostgroups.customer_server_id = ". $id;
						//print_r($phql);
						$this->modelsManager->executeQuery($phql);
						$unencodedArray = ['code' => 200];

						header('Content-type: application/json');
            echo json_encode($unencodedArray);
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
