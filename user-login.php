<?
include("User.php");

$json = new Services_JSON();
$jc = file_get_contents('php://input');
$p = $json->decode($jc, true);

$rs = csUser::Login($p->email, $p->pwd);


print_r($rs);
print_r(csUser::GetNearestUsers('45.07489','7.68002','10'));



?>