<?
include 'mm-config.php';

$json = new Services_JSON();
$jc = file_get_contents('php://input');
$p = $json->decode($jc, true);

$res = csUser::UpdateLocation($p->userKey , $p->latitude, $p->longitude);

$arr = array();
$json = new Services_JSON();
$output = $json->encode($arr);
csHttpHelper::SendResponse($output);
?>
