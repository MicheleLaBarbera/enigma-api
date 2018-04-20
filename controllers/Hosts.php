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
              $request = "GET hostsbygroup\nColumns: host_address host_alias groups host_num_services_crit host_num_services_ok host_num_services_unknown host_num_services_warn display_name\nFilter: groups >= ". $robot->group ."\n";

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
}
?>
