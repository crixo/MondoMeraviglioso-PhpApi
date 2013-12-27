<?php
include "test-config.php";
include "../json.php";


//Login
$data = array("email" => "test.mm.01@gmail.com", "pwd" => "test");  
$result = post('user-login.php', $data);
$passed = $result['statusCode'] == 200 && $result['responseType'] == 'application/json';
echo sprintf('It should be possible to login: %s - [%s] %s', 
$passed? 'passed' : 'failed', 
$result['statusCode'],
$passed?  '' : $result['body']);

echo '<hr />';

$data = array("email" => "test.mm.01@gmail.com", "pwd" => "wrong-pwd");  
$result = post('user-login.php', $data);
echo sprintf('It should not be possible to login with wrong pwd: %s - [%s]', 
$result['statusCode'] == 401? 'passed' : 'failed', 
$result['statusCode']);

echo '<hr />';

$data = array("email" => "wrong-email@gmail.com", "pwd" => "test");  
$result = post('user-login.php', $data);
echo sprintf('It should not be possible to login with a not existing email: %s - [%s]', 
$result['statusCode'] == 401? 'passed' : 'failed', 
$result['statusCode']);

echo '<hr />';

//Create
$guid = str_pad(mt_rand(1000,99999999999999999999), 32, '0', STR_PAD_LEFT);
$data = array("key" => $guid, "email" => $guid."@gmail.com", "pwd" => "test", "screenName" => $guid, "type" => "0", "thumbnail" => "test");  
$result = post('user-create.php', $data);
$passed = $result['statusCode'] == 200 && $result['responseType'] == 'application/json';
echo sprintf('It should be possible to create a new user: %s - [%s] %s', 
$passed? 'passed' : 'failed', 
$result['statusCode'],
$passed?  '' : $result['body']);//
echo '<hr />';

//User Nearest
$data = array("lat" => "45.07489", "lon" => "7.68002", "dist" => "10", "limit" => "100");  
$result = get('user-nearest.php', $data);
echo sprintf('It should be possible to load nearest users: %s - [%s] %s', 
$result['statusCode'] == 200 && $result['responseType'] == 'application/json'? 'passed' : 'failed', 
$result['statusCode'],
'');

echo '<hr />';






function post($url, $requestData)
{
	$json = new Services_JSON();                                                                  
	$data_string = $json->encode($requestData);                                                                          
 
	$ch = curl_init(API_BASE_URL . $url);                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
		'Content-Type: application/json',      
		'Accept: application/json',                                                                          
		'Content-Length: ' . strlen($data_string))                                                                       
	);                                                                                                                   

	$response = array();
	$response['body'] = curl_exec($ch);
	$response['statusCode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$response['responseType'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

	curl_close($ch);

	return $response;
}

function get($url, $requestData)
{
if(!function_exists('http_build_query'))
{
function http_build_query($arr)
{
	$qs = array();
	foreach($arr as $k=>$v)
	{
		array_push($qs, $k.'='.urlencode($v));
	}
	return implode('&', $qs);
}

}

	$json = new Services_JSON();       
	
	if(count($requestData)>0)
	{
		$url .= (strpos($url, '?') !== FALSE)? '&' : '?';        
		$url .= http_build_query($requestData);
	}                                    
 
	$ch = curl_init(API_BASE_URL . $url);                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                       
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(     
		'Accept: application/json'                                                                      
	));                                                                                                                   

	$response = array();
	$response['body'] = curl_exec($ch);
	$response['statusCode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$response['responseType'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

	curl_close($ch);

	return $response;
}
?>