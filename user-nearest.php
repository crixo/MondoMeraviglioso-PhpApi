<?
include 'mm-config.php';
include 'User.php';

$lat = $_GET['lat'];
$lon = $_GET['lon'];
$dist = $_GET['dist'];
$skip = $_GET['skip'];
$limit = $_GET['limit'];
$userKey = $_GET['userKey'];

$allUsers = csUser::GetNearestUsers($lat, $lon, $dist, $limit);
$nearestUsers = array();
foreach ($allUsers as $user) {
    if($user['key']!=$userKey){
    	array_push($nearestUsers, $user);
    }
}

$usersAsArray = array();
$usersAsArray['users'] = $nearestUsers;

$json = new Services_JSON();
$output = $json->encode($usersAsArray);
csHttpHelper::SendResponse($output);
?>