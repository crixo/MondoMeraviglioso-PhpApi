<?php

class csUser 
{
	function Login( $email, $pwd ){
		$qry = sprintf("
			SELECT 
				HEX(`key`) as 'key', email, `type`, screen_name as screenName
			FROM
				mm_user
			WHERE 
				email='%s' AND pwd=PASSWORD('%s')",
            $email,
            $pwd);

		$res = dbTools::GetRecordSet( $qry );
		
		return $res->getNumRows()==1 ? $res->convertToArray()[0] : null;	
	}
	
	function GetNearestUsers( $lat, $lon, $dist, $limit=10 ){
		$qry = sprintf("
SELECT 
HEX(`key`) as 'key',email,screen_name,`type`,lat,lon,
6368 * 2 * ASIN(SQRT(POWER(SIN((%s - abs(dest.lat)) *
pi()/180 / 2), 2) + COS(%s * pi()/180 ) * COS(abs(dest.lat) *
pi()/180) * POWER(SIN((%s - dest.lon) * pi()/180 / 2), 2) )) as distance
FROM mm_user u
INNER JOIN mm_user_location dest ON u.'key' = dest.user_key
having distance < %s
ORDER BY distance limit %s;",
            $lat,
            $lat,
            $lon,
            $dist,
            $limit);

		$res = dbTools::GetRecordSet( $qry );
		
		return $res->convertToArray();	
	}
	
	// ---------------------------------------------------------------
	
	function UpdateLocation($userKey, $lat, $lon){
		$qry = sprintf("
			UPDATE 
				mm_user_location
			SET 
				lat=%s,
				lon=%s,
				locationUpdatedAt=UTC_TIMESTAMP()
			WHERE
				'user_key' = UNHEX('%s')",
            $lat,
            $lon,
            $userKey);

		dbTools::SqlAction( 'upd', $qry );			
	}
	
	
	// ---------------------------------------------------------------


	function Update(){
		$qry = '
			UPDATE 
				mm_user
			SET 
				first_name=\''. csBaseContent::LabelForDb($this -> _FirstName) .'\',
				last_name=\''. csBaseContent::LabelForDb($this -> _LastName) .'\',
				email=\''. $this -> _Email .'\'
			WHERE
				id_author = '.$id;

		dbTools::SqlAction( 'upd', $qry );			
	}

	
	// ---------------------------------------------------------------
	
	
	function Create($createCommand){		
     $qry = sprintf("
INSERT INTO `mm_user` (`key`, `email`, `type`, `screen_name`, `pwd`, `createdAt`) 
VALUES
(UNHEX('%s'), '%s', %s, '%s', %s, %s, PASSWORD('%s'), UTC_TIMESTAMP())
         ",
		$createCommand -> userKey,
		$createCommand -> email,
		$createCommand -> type,
		$createCommand -> screenName,
		$createCommand -> pwd);

      dbTools::SqlAction( 'ins', $qry );
	}	
	
}

?>