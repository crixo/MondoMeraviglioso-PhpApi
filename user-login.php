<?
include 'mm-config.php';

$json = new Services_JSON();
$jc = file_get_contents('php://input');
$p = $json->decode($jc, true);

$userAsArray = csUser::Login($p->email, $p->pwd);

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