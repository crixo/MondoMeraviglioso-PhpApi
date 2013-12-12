<?
include 'mm-config.php';

$lat = $_GET['lat'];
$lon = $_GET['lon'];
$dist = $_GET['dist'];
$skip = $_GET['skip'];
$limit = $_GET['limit'];

$res = csUser::GetNearestUsers($lat, $lon, $dist, $limit);

$arr = array();
for( $r=0; $r<$res->getNumRows(); $r++ ){
	for( $c=0; $c<$res->getNumCols(); $c++ ){
		$arr[$r][$res->getColName($c)] = $res->getColumn_by_num( $r, $c);
	}
}

$json = new Services_JSON();
$output = $json->encode($arr);
csHttpHelper::SendResponse($output);



?>