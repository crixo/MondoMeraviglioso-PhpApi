<?
include 'mm-config.php';

$tables = array('mm_user_thumbnail'=>'user_key', 'mm_user_message'=>'key');
$key = $_GET['key'];

foreach($tables as $table => $column)
{
     $qry = sprintf("
	SELECT `thumbnail` FROM %s WHERE `%s`=UNHEX('%s')
         ",
		$table,
		$column,
		$key);
		
    $thumbnail = dbTools::EseguiQueryScalare($qry);
    
	if($thumbnail != NULL_VALUE)
	{
		header('Content-type: image/jpg');
		echo base64_decode($thumbnail);
		exit();
	}
}
      
echo "No image found";

?>