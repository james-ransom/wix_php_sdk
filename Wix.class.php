<?php


/**
* Wix simple working php sdk 
* ransom1538@gmail.com
*/

class Wix 
{

public $secret; 
public $app_key; 

function __construct($app_key, $secret) 
{
	$this->secret = $secret; 
	$this->app_key = $app_key; 
}

public function encode_signature( $instance_id, $method, $request_path, $query_params, $post_params, $body = '')
{
    //This form of timestamp was posted on the communties however it doesnt' work 
    //and causes: Required parameter/header [timestamp/x-wix-timestamp] has illegal value [2014-11-10T17:23:10-06:00]
    $ts = date('Y-m-d\TH:i:s') . substr(microtime(), 1, 4) . 'Z';

    $request_params = array_merge($query_params, $post_params);
    $request_params['application-id'] = $this->app_key;
    $request_params['instance-id'] = $instance_id;
    $request_params['timestamp'] = $ts;
    $request_params['version'] = "1.0.0";
    
    ksort($request_params);
    $signature_string = strtoupper($method) . "\n$request_path\n";
    foreach ($request_params as $request_param)
    {
        switch(gettype($request_param))
        {
        	case 'boolean':
                $signature_string .= $request_param ? "true\n" : "false\n";
        		break;
        	default:
                $signature_string .= strval($request_param) . "\n";
        		break;
        }
    }
    $signature_string = trim($signature_string);
    
    if($body != NULL && strlen($body) > 0) {
        $signature_string .= "\n" . $body;
    }

    $encoded_signature = strtr(base64_encode(hash_hmac("sha256", $signature_string, $this->secret , TRUE)), '+/', '-_');
    while(substr($encoded_signature, -1) == '=')
    {
        $encoded_signature = substr($encoded_signature, 0, -1);
    }

    return array('sig' => $encoded_signature, 'ts' => $ts);
}


public function get_wix_json() 
{
	list( $code, $data ) = explode( '.', $_GET[ 'instance' ] );

	if ( base64_decode( strtr( $code, "-_", "+/" ) ) != hash_hmac( "sha256", $data, $this->secret, TRUE ) ) {
		echo "Unable to get instance"; 
		return false; 
	}
	if ( ( $json = json_decode( base64_decode( $data ) ) ) === null ) {
		echo "Unable to get instance"; 
		return false; 
	}
	return $json;
}


public function get_contacts($instance_id) 
{
	$signatures = $this->encode_signature( $instance_id, 'GET', '/v1/contacts', array(), array(),  ''); 
	$url = 'https://openapi.wix.com/v1/contacts';
	$url .= "?timestamp=". ($signatures['ts']) . "&application-id=".($this->app_key)."&instance-id=".  ($instance_id)."&signature=".$signatures['sig']."&version=1.0.0";; 
	return ($this->curl_request('GET', $url , array()));
}


public function get_sites( $instance_id)
{
	$signatures = $this->encode_signature( $instance_id, 'GET', '/v1/sites/site', array(), array(),  '');
        $url = 'https://openapi.wix.com/v1/sites/site';
        $url .= "?timestamp=". ($signatures['ts']) . "&application-id=".($this->app_key)."&instance-id=".  ($instance_id)."&signature=".$signatures['sig']."&version=1.0.0";;
        return ($this->curl_request('GET', $url , array()));
}


public function curl_request($method, $uri, $data = '')
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uri);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);

    if ('POST' == $method)
    {
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    else if ('PUT' == $method)
    {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    else if('GET' != $method)
    {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }

    $response = curl_exec($ch);
    return $response; 
}



}

?>
