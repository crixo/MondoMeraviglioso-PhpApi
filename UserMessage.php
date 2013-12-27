<?php

class csUserMessage
{	
	// ---------------------------------------------------------------
	
	function Create($command){		
     $qry = sprintf("
	INSERT INTO `mm_user_message` (`key`, `user_key_sender`, `user_key_receiver`, `title`, `body`, `thumbnail`, `sent_at`) 
	VALUES
	(UNHEX('%s'), UNHEX('%s'), UNHEX('%s'), '%s', '%s', '%s', UTC_TIMESTAMP())
         ",
		$createCommand -> key,
		$createCommand -> email,
		$createCommand -> type,
		$createCommand -> screenName,
		$createCommand -> pwd);
		
      $res = dbTools::SqlAction( 'ins', $qry );
      
      return $res;
	}	
	
	// ---------------------------------------------------------------
	
}

?>