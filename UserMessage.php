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
		$command -> messageKey,
		$command -> senderKey,
		$command -> recipientKey,
		$command -> title,
		$command -> body,
		$command -> thumbnail);
		
      $res = dbTools::SqlAction( 'ins', $qry );
      
      return $res;
	}	
	
	// ---------------------------------------------------------------
	
}

?>