<?php
include "../json.php";

DEFINE( 'API_BASE_URL', "http://mm/" );

//Login
$data = array("email" => "test.mm.01@gmail.com", "pwd" => "test");  
$result = post('user-login.php', $data);
echo sprintf('It should be possible to login: %s - %s', $result['statusCode'] == 200? 'passed' : 'failed', $result['statusCode']);

echo '<br />';

$data = array("email" => "test.mm.01@gmail.com", "pwd" => "wrong-pwd");  
$result = post('user-login.php', $data);
echo sprintf('It should not be possible to login with wrong pwd: %s - %s', $result['statusCode'] == 401? 'passed' : 'failed', $result['statusCode']);

echo '<br />';

$data = array("email" => "wrong-email@gmail.com", "pwd" => "test");  
$result = post('user-login.php', $data);
echo sprintf('It should not be possible to login with a not existing email: %s - %s', $result['statusCode'] == 401? 'passed' : 'failed', $result['statusCode']);

echo '<br />';

//Create
$data = array("email" => "test.mm.01@gmail.com", "pwd" => "test");  
$result = post('user-login.php', $data);
echo sprintf('It should be possible to create a new user: %s - %s', $result['statusCode'] == 200? 'passed' : 'failed', $result['statusCode']);

echo '<br />';





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

	curl_close($ch);

	return $response;
}

function get($url)
{
	$json = new Services_JSON();                                                                                                   
 
	$ch = curl_init(API_BASE_URL . $url);                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                        
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