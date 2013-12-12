<?
include 'mm-config.php';

$json = new Services_JSON();
$jc = file_get_contents('php://input');
$createCommand = $json->decode($jc, true);

$res = csUser::Create($createCommand);

$arr = array();
$json = new Services_JSON();
$output = $json->encode($arr);
csHttpHelper::SendResponse($output);
?>
