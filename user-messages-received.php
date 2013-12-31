<?
include 'mm-config.php';
include 'UserMessage.php';


$userKey = $_GET['userKey'];

$messages = csUserMessage::GetMessagesFor($userKey);
$arrayWrapper = array();
$arrayWrapper['messages'] = $messages;

$json = new Services_JSON();
$output = $json->encode($arrayWrapper);
csHttpHelper::SendResponse($output);
?>