<?
include 'mm-config.php';
include 'User.php';

$json = new Services_JSON();
$jc = file_get_contents('php://input');
$jsonData = $json->decode($jc, true);

$res = csUser::TraceLogout($jsonData -> userKey);

if(strlen($res['error']) == 0)
{
$arr = array();
$json = new Services_JSON();
$output = $json->encode($arr);
csHttpHelper::SendResponse($output);
}
else
{
csHttpHelper::SendResponse('user logout failed: '. $res['error'], 500, 'text/plain');
}
?>
