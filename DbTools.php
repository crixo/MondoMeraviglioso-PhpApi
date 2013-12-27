<?

class dbTools
{
   function GetConnString(){
      $arrConnStr = array();
      $arrConnStr[1] = 'dbname='.DB_NAME.' password='.DB_PWD.' user='.DB_USER;
      $arrConnStr[2] = 'host='.DB_HOST.' username='.DB_USER.' dbname='.DB_NAME.' password='.DB_PWD;

      return $arrConnStr[DB_TYPE];
   }

   function CreaConn($dbstring='', $isTrans=false){
		
      $dbstring = dbTools::GetConnString();
         $conn = new connection;
         $conn -> open($dbstring);
      return $conn;
   }

	/**
		* Metodo usato solo per chiudere connessioni in transazione
		* @static 
		*/
   function CloseConn($Conn){
      if( $Conn -> IsTransaction && dbTools::DbSupportTransaction() ){
         $Conn -> Close();
      }

      if( isset($GLOBALS["GLOBAL_CONN"]) && $GLOBALS["GLOBAL_CONN"]->IsClose ){
         $GLOBALS["GLOBAL_CONN"] = dbTools::CreaConn();
      }
   }


   // ---------------------------------------------------------------


	function GetRecordSet( $qry ){
     	$conn = dbTools::CreaConn();
		$res = $conn -> runSQL($qry);
		return $res;
	}


   // ---------------------------------------------------------------


   function EseguiQueryScalare( $qry )
   {

		$conn = dbTools::CreaConn();

      	$res = $conn -> runSQL($qry);//dbTools::GetRecordSet( $qry, $conn, $isLastCommand );

		$valToRet = NULL_VALUE;
		if($res->getNumRows() > 0)
		{
			$valToRet = $res->getColumn_by_num(0,0);
			if(strlen($valToRet)==0 && $res->getNumRows()==1)
			{
				$valToRet = NULL_VALUE;
			}
		}

      return $valToRet;
   }


   // ---------------------------------------------------------------


	function SqlAction($action, $qry, $sequence_name= 'null'){
		$idToRet = -1;
		
		$conn = dbTools::CreaConn();
		$errMsg = $conn -> runActionQuery($qry);
		
		if(strlen($errMsg)>0){
         $conn->close("ROLLBACK");
         IF(DEBUG)echo $errMsg;
         unset($conn);
		}else{	
			if( $action == 'ins' ){
				if( DB_TYPE==2 && $sequence_name != 'null' )
					$idToRet = mysql_insert_id();
				else if( DB_TYPE==1 && $sequence_name != '')
					$idToRet = dbTools::EseguiQueryScalare("SELECT currval('". $sequence_name ."')", $conn, false);
			}
		}
		
		$result = array();
		$result['sequence'] = $idToRet;
		$result['error'] = $errMsg;
		
		return $result;
	}


	// ---------------------------------------------------------------


	function SqlLimit( $limit, $offset=0 ){

		if( $limit > 0 ){
			$qryLimit = ( DB_TYPE == 1)? " LIMIT $limit OFFSET $offset" : " LIMIT $offset,$limit";
		}else
			$qryLimit = '';

		return $qryLimit;
	}


   // ---------------------------------------------------------------


   function CheckNullValue($val){
      $isNull = false;
      if( is_null($val) || strlen($val)==0 ){
         $val = NULL_VALUE;
         $isNull = true;
      }

      return ($isNull)? $val:"'$val'";
   }


   // ---------------------------------------------------------------


   function IsSecureSort( $sSort ){
      $bRes = true;
      if(strpos($sSort,";")!==false)
         $bRes = false;


      return $bRes;
   }


   // ---------------------------------------------------------------

	
	function SqlDatePart( $strColumnName, $strPart){
		if( DB_TYPE == 1)
			$str = "date_part('".$strPart."', ".$strColumnName.")";
		else if( DB_TYPE == 2)
			$str = strtoupper($strPart)."(".$strColumnName.")";
			
		return $str;
	}	
	
	
	// ---------------------------------------------------------------

	
	function RandomId( $qry ){

		$res = dbTools::GetRecordSet($qry);
		
		$randomRow = rand( 0, $res -> getNumRows()-1 );
		
		$randomId = $res -> getColumn_by_num( $randomRow, 0 );

		return $randomId;		
	}	

	// ---------------------------------------------------------------
	// PRIVATE
	function errManager( $msg, $qry ){
		echo "<div class=\"ko\">$msg</div><div class=\"query\">$qry</div>";
		
		global $ARR_EMAILS;
		

		$emailDest = $emailWebMaster;
		$soggetto = "BackOffice ". $strtoupper(NOME_SITO) ." | Errore sul DB";
		$messaggio = "Query di errore:\n".$qry;
		$intestazioni = "";
		$intestazioni .= "From: BackOffice ". $strtoupper(NOME_SITO) ." <". $ARR_EMAILS["BackOffice"] .">\n";
		$intestazioni .= "X-Sender: <". $ARR_EMAILS["BackOffice"] .">\n";
		$intestazioni .= "X-Mailer: PHP\n"; // mailer
		$intestazioni .= "X-Priority: 1\n"; // Messaggio urgente!
		$intestazioni .= "Return-Path: <". $ARR_EMAILS["WebMaster"] .">\n";  // Indirizzo di ritorno per errori		
		//mail($emailDest, $soggetto, $messaggio, $intestazioni);
	}
}
?>