<?
include 'mm-config.php';

$lat = $_GET['lat'];
$lon = $_GET['lon'];
$dist = $_GET['dist'];
$skip = $_GET['skip'];
$limit = $_GET['limit'];

$usersAsArray = csUser::GetNearestUsers($lat, $lon, $dist, $limit);

$json = new Services_JSON();
$output = $json->encode($usersAsArray);
csHttpHelper::SendResponse($output);
?>