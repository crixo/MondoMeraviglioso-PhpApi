<?
include 'mm-config.php';
include 'UserMessage.php';


$json = new Services_JSON();
$jc = file_get_contents('php://input');
$command = $json->decode($jc, true);

$userAsArray = csUserMessage::Create($command);

if($userAsArray != null)
{
$json = new Services_JSON();
$output = $json->encode($userAsArray);
csHttpHelper::SendResponse($output);
}
else
{
csHttpHelper::SendResponse('login failed', 401, 'text/plain');
}
?>