<?php

class csUser 
{
	function Login( $email, $pwd )
	{
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
		
		if($res->getNumRows()==1)
		{
			$arr = $res->convertToArray();
			$userData = $arr[0];
			csUser::TraceLogin($userData['key']);
			return $userData;
		}
		
		return  null;	
	}
	
	// ---------------------------------------------------------------
	
	function GetNearestUsers( $lat, $lon, $dist, $limit=10 )
	{
		$qry = sprintf("
SELECT
	HEX(l.user_key) as 'key',
	l.lat,l.lon,
	l.distance,		
	u.email,u.screen_name as screenName,u.`type`,
	t.thumbnail
FROM
(
	SELECT 
		`user_key`,
		lat,lon,
		6368 * 2 * ASIN(SQRT(POWER(SIN((%s - abs(dest.lat)) *
		pi()/180 / 2), 2) + COS(%s * pi()/180 ) * COS(abs(dest.lat) *
		pi()/180) * POWER(SIN((%s - dest.lon) * pi()/180 / 2), 2) )) as distance
	FROM mm_user_location dest
	HAVING distance < %s
	ORDER BY distance limit %s
) l
INNER JOIN mm_user u ON l.user_key = u.`key`
LEFT JOIN 
(
	SELECT 
		user_key, MAX(thumbnail) as thumbnail 
	FROM 
		mm_user_thumbnail 
	GROUP BY user_key
) t ON l.user_key = t.`user_key`
",
            $lat,
            $lat,
            $lon,
            $dist,
            $limit);

		$res = dbTools::GetRecordSet( $qry );
		
		$arr = $res->convertToArray();
		
		return $arr;
	}
	
	// ---------------------------------------------------------------
	
	function UpdateLocation($userKey, $lat, $lon){
		$qry = sprintf("
			UPDATE 
				mm_user_location
			SET 
				lat=%s,
				lon=%s,
				updated_at=UTC_TIMESTAMP()
			WHERE
				user_key = UNHEX('%s')",
            $lat,
            $lon,
            $userKey);
            
        echo  $qry;

		dbTools::SqlAction( 'upd', $qry );			
	}
	
	
	// ---------------------------------------------------------------


	function TraceLogin($userKey, $forceInsert = false){
		$count = 0;
		
		if(!$forceInsert)
		{
			$qry = sprintf("
				SELECT count(*)
				FROM mm_user_online
				WHERE user_key = UNHEX('%s')",
				$userKey);

			$count = dbTools::EseguiQueryScalare($qry );	
		}
		
		if($count == 0)
		{
			$qry = sprintf("
			INSERT INTO 
				mm_user_online
			(user_key, login_at)
			VALUES
			(UNHEX('%s'), UTC_TIMESTAMP())",
            $userKey);

			dbTools::SqlAction( 'ins', $qry );	
		}
		else
		{
			$qry = sprintf("
			UPDATE 
				mm_user_online
			SET
				login_at = UTC_TIMESTAMP(),
				logout_at = NULL
			WHERE
				user_key = UNHEX('%s')",
            $userKey);

			dbTools::SqlAction( 'upd', $qry );	
		}		
	}

	
	// ---------------------------------------------------------------


	function TraceLogout($userKey){
		$qry = sprintf("
			UPDATE 
				mm_user_online
			SET
				logout_at = UTC_TIMESTAMP()
			WHERE
				user_key = UNHEX('%s')",
            $userKey);

		dbTools::SqlAction( 'upd', $qry );			
	}

	
	// ---------------------------------------------------------------
	
	
	function Create($createCommand){		
     $qry = sprintf("
	INSERT INTO `mm_user` (`key`, `email`, `type`, `screen_name`, `pwd`, `created_at`) 
	VALUES
	(UNHEX('%s'), '%s', %s, '%s', PASSWORD('%s'), UTC_TIMESTAMP())
         ",
		$createCommand -> key,
		$createCommand -> email,
		$createCommand -> type,
		$createCommand -> screenName,
		$createCommand -> pwd);
		
      $res = dbTools::SqlAction( 'ins', $qry );
      
      if(strlen($res['error']) == 0)
      {
		 $qry = sprintf("
	INSERT INTO `mm_user_location` (`user_key`, `lat`,  `lon`, `updated_at`) 
	VALUES
	(UNHEX('%s'), '0', '0', UTC_TIMESTAMP())
			 ",
			$createCommand -> key);

		  $res = dbTools::SqlAction( 'ins', $qry );      	
      }
      
      if(strlen($res['error']) == 0 && strlen($createCommand -> thumbnail) > 0)
      {
		 $qry = sprintf("
	INSERT INTO `mm_user_thumbnail` (`user_key`, `thumbnail`,  `added_at`) 
	VALUES
	(UNHEX('%s'), '%s', UTC_TIMESTAMP())
			 ",
			$createCommand -> key,
			$createCommand -> thumbnail);

		  $res = dbTools::SqlAction( 'ins', $qry );      	
      }
      
      if(strlen($res['error']) == 0)
      {
		  csUser::TraceLogin($createCommand -> key, true);      	
      }      
      
      return $res;
	}	
	
}

?>