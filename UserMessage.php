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
	
	function GetMessagesFor( $userKey, $skip=0, $limit=1000 )
	{
		$qry = sprintf("
SELECT
	HEX(m.`key`) as 'key',
	m.`user_key_sender` as 'senderKey',
	u.`screen_name` as 'senderScreenName',
	m.title,m.sent_at as 'sentAt',m.read_at as 'readAt'
FROM `mm_user_message` m
INNER JOIN mm_user u ON m.user_key_sender = u.`key`
WHERE m.`user_key_receiver` = UNHEX('%s')
ORDER BY m.sent_at DESC
LIMIT %s,%s
",
            $userKey,
            $skip,
            $limit);

		$res = dbTools::GetRecordSet( $qry );
		
		$arr = $res->convertToArray();
		
		return $arr;
	}	
	
	// ---------------------------------------------------------------
	
	function GetMessage( $key )
	{
		$qry = sprintf("
SELECT
	HEX(m.`key`) as 'key',
	m.`user_key_sender` as 'senderKey',
	u.`screen_name` as 'senderScreenName',
	m.title, m.body,m.thumbnail, m.sent_at as 'sentAt', m.read_at as 'readAt'
FROM `mm_user_message` m
INNER JOIN mm_user u ON m.user_key_sender = u.`key`
WHERE m.`key` = UNHEX('%s')
",
            $key);

		$res = dbTools::GetRecordSet( $qry );
		
		if($res->getNumRows()==1)
		{
			$arr = $res->convertToArray();
			$msg = $arr[0];
			csUserMessage::MarkAsRead($msg['key']);
			return $msg;
		}
		
		return  null;	
	}	
	
	// ---------------------------------------------------------------
	
	function MarkAsRead( $key )
	{
		$qry = sprintf("
UPDATE
	`mm_user_message`
SET
	read_at = UTC_TIMESTAMP()
WHERE 
	`key` = UNHEX('%s') AND read_at IS NULL
",
            $key);

		return dbTools::SqlAction( 'upd', $qry );
	}		
	
}

?>