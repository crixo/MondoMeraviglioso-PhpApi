<?php

require_once 'mm-config.php';

class csUser 
{
	function Login( $email, $pwd ){
		$qry = sprintf("
			SELECT 
				HEX(`key`),email,lat,lon,`type`,screenName 
			FROM
				mm_user
			WHERE 
				email='%s' AND pwd='%s'",
            $email,
            $pwd);

		return dbTools::GetRecordSet( $qry );
	}
	
	function GetNearestUsers( $lat, $lon, $dist, $limit=10 ){
		$qry = sprintf("
SELECT 
HEX(`key`) as 'key',email,screenName,`type`,lat,lon,
6368 * 2 * ASIN(SQRT(POWER(SIN((%s - abs(dest.lat)) *
pi()/180 / 2), 2) + COS(%s * pi()/180 ) * COS(abs(dest.lat) *
pi()/180) * POWER(SIN((%s - dest.lon) * pi()/180 / 2), 2) )) as distance
FROM mm_user dest
having distance < %s
ORDER BY distance limit %s;",
            $lat,
            $lat,
            $lon,
            $dist,
            $limit);

		return dbTools::GetRecordSet( $qry );
	}
	
	
   function Store(){
   	if($this -> _ID == -1) $this -> insert();
   	else  $this -> update();
   	
   	return true;
   }
	
	
	// ---------------------------------------------------------------


	function update(){
		$qry = '
			UPDATE 
				lkp_author
			SET 
				first_name=\''. csBaseContent::LabelForDb($this -> _FirstName) .'\',
				last_name=\''. csBaseContent::LabelForDb($this -> _LastName) .'\',
				email=\''. $this -> _Email .'\'
			WHERE
				id_author = '.$id;

		dbTools::SqlAction( 'upd', $qry );			
	}

	
	// ---------------------------------------------------------------
	
	
	function insert(){		
      $qry = '
      	INSERT INTO lkp_author 
         ( first_name, last_name, email )
         VALUES
         ( '. csBaseContent::LabelForDb($this -> _FirstName) .'\', \''. csBaseContent::LabelForDb($this -> _LastName) .'\', \''. $this -> _Email .'\',)';

      dbTools::SqlAction( 'ins', $qry );
	}
	
	/*
		@override
		@static
		@return collection of current object
	*/
	function GetList(){
		$arr = null;
	 	return $arr;
	}	
	
}

?>