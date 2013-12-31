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
$guid = str_pad(mt_rand(1000,999999999), 32, '0', STR_PAD_LEFT);
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

//Send Message
$guid = str_pad(mt_rand(1000,999999999), 32, '0', STR_PAD_LEFT);
$data = array(
	"messageKey" => $guid, 
	"senderKey" => "00000000000000000000000000000001", 
	"recipientKey" => "00000000000000000000000000000002", 
	"title" => "message title", 
	"body" => "message body", 
	"thumbnail" => "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");  
$result = post('user-message-create.php', $data);
$passed = $result['statusCode'] == 200 && $result['responseType'] == 'application/json';
echo sprintf('It should be possible to send a message: %s - [%s] %s', 
$passed? 'passed' : 'failed', 
$result['statusCode'],
$passed?  '' : $result['body']);//
echo '<hr />';

// Messages For
$data = array("userKey" => "00000000000000000000000000000002");
$result = get('user-messages-received.php', $data);
$passed = passed($result);
echo sprintf('It should be possible to get list of received messages: %s - [%s] %s', 
$passed? 'passed' : 'failed', 
$result['statusCode'],
$passed?  '' : $result['body']);
echo '<hr />';


function passed($result)
{
	return 
		$result['statusCode'] == 200 && 
		$result['responseType'] == 'application/json' && 
		substr( $result['body'], 0, 1 ) == '{';
}

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