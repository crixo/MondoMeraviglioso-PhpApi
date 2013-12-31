<?
include 'mm-config.php';
include 'UserMessage.php';


$key = $_GET['key'];

$message = csUserMessage::GetMessage($key);

$json = new Services_JSON();
$output = $json->encode($message);
csHttpHelper::SendResponse($output);
?>