<?php
namespace Controller;

use \Firebase\JWT\JWT;
use Phalcon\Mvc\Controller;

class Services extends Controller
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
              $request = "GET services\nColumns: display_name service_plugin_output service_last_state_change service_state host_name service_last_check\nFilter: host_name = ". $robot->name ."\n";
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

        					$array[$count]['name'] = $exploded_values[0];
									$array[$count]['status'] = $exploded_values[1];
        					$array[$count]['age'] = date("d-m-Y", $exploded_values[2]);
									$array[$count]['age_min'] = date("H:i:s", $exploded_values[2]);
        					$array[$count]['state'] = $exploded_values[3];
									$array[$count]['h_name'] = $exploded_values[4];
									$array[$count]['last_check'] = date("d-m-Y H:i:s", $exploded_values[5]);


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
