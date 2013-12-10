<?php
    
include("json.php");
if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data) {
    	//echo $filename;
    	//echo $data;
        $f = @fopen($filename, 'a');
		$bytes = fwrite($f, $data);
		fclose($f);
		return $bytes;
    }
}
    
$json = new Services_JSON();
     /*
$a = $json->encode( array( 'a'=>1, '2'=>2, 'c'=>'I <3 JSON' ) );
echo $a;
// Outputs: {"a":1,"b":2,"c":"I <3 JSON"}
$b = $json->decode( $a );
echo "$b->a, $b->b, $b->c";

echo "<br />";

    
    $phpObj = json_decode($_GET['json']);
    //echo count($phpObj);
    //print_r($phpObj);
    
    //echo($phpObj['test']);
    print $phpObj->{'test'}; // 12345
    
    file_put_contents('/Users/cristiano.degiorgis/Sites/mm/user-location.txt', "$b->a, $b->b, $b->c", FILE_APPEND);
   */ 
   $jc = file_get_contents('php://input');
    $p = $json->decode($jc, true);
    //print_r($p);
    //print_r($data);
	//echo $p[0];
	//echo "$p->userKey";
	$line = $p->userKey .  ',' . $p->ts . ',' . $p->latitude . ',' . $p->longitude . PHP_EOL;
    
    echo $line;
    file_put_contents(getcwd().'/user-location.txt', $line, FILE_APPEND);
?>
