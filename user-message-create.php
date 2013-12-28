<?
include 'mm-config.php';
include 'UserMessage.php';


$json = new Services_JSON();
$jc = file_get_contents('php://input');
$command = $json->decode($jc, true);

$res = csUserMessage::Create($command);

if(strlen($res['error']) == 0)
{
csHttpHelper::SendResponse("{}");
}
else
{
csHttpHelper::SendResponse('sending message failed:' . $res['error'], 500, 'text/plain');
}
?>