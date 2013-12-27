<?
include 'mm-config.php';
include 'User.php';

$json = new Services_JSON();
$jc = file_get_contents('php://input');
$createCommand = $json->decode($jc, true);

$res = csUser::Create($createCommand);

if(strlen($res['error']) == 0)
{
$arr = array();
$json = new Services_JSON();
$output = $json->encode($arr);
csHttpHelper::SendResponse($output);
}
else
{
csHttpHelper::SendResponse('user creation failed: '. $res['error'], 500, 'text/plain');
}
?>
