<?

/* -------------------------------------------------------------------------------------- *\

CLASSE PER L'INTERROGAZIONE DEL DATABASE
	STATICA

\* -------------------------------------------------------------------------------------- */
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


	function GetRecordSet( $qry, $conn=null, $isLastCommand=true ){

      if(is_null($conn)){
         $conn = dbTools::CreaConn();
      }
		$res = $conn -> runSQL($qry);

      /*
      if($checkClose && $isLastCommand){
        $conn -> close();
        unset($conn);
      }
      */
	
		return $res;
	}

   function TestConn($k,$contesto){
      if(!isset($GLOBALS["PageState"][$k]))$GLOBALS["PageState"][$k] = 0; else $GLOBALS["PageState"][$k] = $GLOBALS["PageState"][$k]+1;
         if( DEBUG )
            echo "$contesto - $k - ".$GLOBALS["PageState"][$k].'<br>';
   }


   // ---------------------------------------------------------------


   function EseguiQueryScalare( $qry, $conn=null, $isLastCommand=true){

      if(is_null($conn)){
         $conn = dbTools::CreaConn();
         /*
         if(isset($GLOBALS["GLOBAL_CONN"])){
            $conn = $GLOBALS["GLOBAL_CONN"];
            $checkClose = false;

            //dbTools::TestConn("cGC", "QueryScalare");
         }else{
            //dbTools::TestConn("cLC", "QueryScalare");
            $conn = dbTools::CreaConn();
         }
         */
      }

      $res = $conn -> runSQL($qry);//dbTools::GetRecordSet( $qry, $conn, $isLastCommand );

      $valToRet = 'null';
      if($res->getNumRows() > 0){
         $valToRet = $res->getColumn_by_num(0,0);
         if(strlen($valToRet)==0 && $res->getNumRows()==1)
            $valToRet = 'null';
      }


      /*
      if($checkClose && $isLastCommand){
        $conn -> close();
        unset($conn);
      }
      */


      return $valToRet;
   }


   // ---------------------------------------------------------------


	function SqlAction($action, $qry){
		$idToRet = -1;
		
		$conn = dbTools::CreaConn();
		$errMsg = $conn -> runActionQuery($qry);
		
		if(strlen($errMsg)>0){
         $conn->close("ROLLBACK");
         unset($conn);
         if(DEBUG) echo $errMsg;
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
         $val = 'null';
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


	function GetTableItems( $ItemsInfo ){
		$arrReplaces = array();
	
		// Impossibile usuare un template in quanto le colonne da mostrare sono dinamiche
		$sTableHeader = $ItemsInfo -> ItemsHeader;
		$sTableFooter = $ItemsInfo -> ItemsFooter;
		
		if(strlen($sTableHeader)==0)
			$sTableHeader = '<table border="0" class="tabBO" cellpadding="0" cellspacing="0">';
											
			
		if( count($ItemsInfo -> SqlBuilder -> ArrQuery) > 0){
			$qryTot		= $ItemsInfo -> SqlBuilder -> ArrQuery["ItemsCount"];
			$qryShow	= $ItemsInfo -> SqlBuilder -> ArrQuery["GetItems"];
		}else{
			$sWhere = "";
			for($i=0; $i<count($ItemsInfo -> SqlBuilder -> ArrWhere); $i++){
				$sWhere .= $ItemsInfo -> SqlBuilder -> ArrWhere[$i]["Column"];
				$sWhere .= $ItemsInfo -> SqlBuilder -> ArrWhere[$i]["TipoConfronto"];
				$sWhere .= $ItemsInfo -> SqlBuilder -> ArrWhere[$i]["Condizione"];
				$sWhere .= $ItemsInfo -> SqlBuilder -> ArrWhere[$i]["Operatore"];
			}
			if( strlen($sWhere)>0 )
				$sWhere .= " WHERE %s";
		
		
			$qryTot = "SELECT count(*) FROM %s%s";				
			$qryTot = sprintf( $qryTot, $ItemsInfo -> SqlBuilder -> Table, $sWhere);
			
			
			$qryShow = "SELECT ";
			$qryShow .= (count($ItemsInfo -> SqlBuilder -> ArrSelectColumns) == 0)? "*":join(',', $ItemsInfo -> SqlBuilder -> ArrSelectColumns);
			$qryShow .= " FROM ".$ItemsInfo -> SqlBuilder -> Table;
			$qryShow .= $sWhere;
			$qryShow .= $ItemsInfo -> SqlBuilder -> OrderBy;
		}
		
		
		// Calcolo il totale assoluto degli Items
		$ItemsInfo -> TotaleItems = dbTools::EseguiQueryScalare( $qryTot );
		
		// Recupero gli Items			
		if( $ItemsInfo -> TotaleItems > 0 ){
			// SQL per la paginazione del recordset
			$qryLimit = ( $ItemsInfo -> UsePagination() )?dbTools::SqlLimit( $ItemsInfo -> ItemsInPage, $ItemsInfo -> Offset ):'';			
			$qryShow .= $qryLimit;
			

			// Lancio la Query
			$res = myTools::GetRecordSet($qryShow);
			$numRows = $res->getNumRows();
			$numCols = $res->getNumCols();
			
			
			// Costruisco l'HeaderTable
			$hasSortCol = ( count($ItemsInfo -> SqlBuilder -> ArrSortColumns) > 0 )? true:false;
			
			$sTableHeader .= '<tr>';
			for($i=0;$i<$numCols;$i++){	
				$strSort='';
				if( $hasSortCol && in_array( $res->getColName($i), $ItemsInfo -> SqlBuilder -> ArrSortColumns) ){
					$str = ' <a href="%s?tab=%s&amp;orderby=%s:ASC"><IMG src="/img/tools/orderAsc.gif" width="15" height="15" border="" alt="Ordina Crescente"></a>';	
					$str .= ' <a href="%s?tab=%s&amp;orderby=%s:DESC"><IMG src="/img/tools/orderDesc.gif" width="15" height="15" border="" alt="Ordina Decrescente"></a>';	
					
					$strSort = sprintf
					(
						$str, 
						$ItemsInfo -> SqlBuilder -> PostPage,
						$ItemsInfo -> SqlBuilder -> Table,
						$res->getColName($i),
						$ItemsInfo -> SqlBuilder -> PostPage,
						$ItemsInfo -> SqlBuilder -> Table,
						$res->getColName($i)
					);
				}
					
				$sTableHeader .= '<th nowrap>';
				$sTableHeader .= $res->getColName($i);
				$sTableHeader .= $strSort;
				$sTableHeader .= '</th>';					
			}
			$sTableHeader .= '</tr>';
			

			
			for($r=0;$r<$numRows ;$r++){				
				$cssClass	= (bcmod( $r, 2 ) == "0")? "P":"D";
				$row = '<tr class="row'. $cssClass .'">';
				
				for($c=0;$c<$numCols;$c++){				
					$value = (string)$res->getColumn_by_num($r,$c);
					if(strlen($value)==0)
						$value="&nbsp;";
					else{
						// formatto le colonne con tipo data
						if(strpos($res->getColName($c), "dt_", 0) !== false)
							$value = myTools::Format('Date', $value);
					}
					$item = sprintf('<td class="item">%s</td>', $value );
				
					$row .= $item;
				}
				
				$row .= '</tr>';				
				
				array_push( $arrReplaces, array($row) );				
			}
			
			if(strlen($sTableFooter)==0)
				$sTableFooter = '</table>';
			
		}else{
			array_push( $arrReplaces, array("<h3>La vista non contiene alcun item</h3>") );
			$sTableHeader = '';
			$sTableFooter = '';
		}
		
		//print_r($arrReplaces);
		
 
		// Aggiorno l'oggeto ItemsInfo
 		//$ItemsInfo -> ArrFind						= $arrFind; // Impossibile usuare un template in quanto le colonne da mostrare sono dinamiche
 		$ItemsInfo -> ArrReplaces				= $arrReplaces;
 		
 		$ItemsInfo -> ItemsHeader = $sTableHeader;
 		$ItemsInfo -> ItemsFooter = $sTableFooter;
		
		return $ItemsInfo;
	}
		
	

   //--------------------------------------------------------------------------


   function TableDump( $Tab, $filename, $mode="txt", $zip=true, $qry="", $writeHeader=true, $sepa=null ){
      require_once GLOBAL_LIB."/obj/excelwriter.inc.php";

      $result = 0;
      $strToWrite = '';

      if($sepa==null)$sepa = array("cell"=>"\t","row"=>"\n");

      if($mode=="xls"){

         $excel = new ExcelWriter( $filename );

         if($excel==false)
            die( $excel->error );
      }

      $qry = (strlen($qry)==0)? "SELECT * FROM $Tab":$qry;
      $res = dbTools::GetRecordSet( $qry );

      if($writeHeader){
         $myArr=array();
         for($i=0;$i<$res->getNumCols();$i++){
            $myArr[] = $res->getColName($i);
         }
         switch($mode){
            case "xls":
               $excel->writeLine($myArr);
               break;
            case "txt":
               $strToWrite .= implode( $sepa["cell"], $myArr).$sepa["row"];
               break;
         }
      }

      for($i=0;$i<$res->getNumRows();$i++){
         $myArr=array();
         for($j=0;$j<$res->getNumCols();$j++)
            $myArr[] = $res->getColumn_by_num($i,$j);

         switch($mode){
            case "xls":
               $excel->writeLine($myArr);
               break;
            case "txt":
               $strToWrite .= implode( $sepa["cell"], $myArr).$sepa["row"];
               break;
         }
      }

      if($mode=="xls")
         $excel->close();



      // creo il file zippato ------------------------------------------
      if($zip){
         if($mode=="xls")
             $strToWrite = csFileManager::leggi($filename);

         if(strlen($strToWrite)>0)
            $result = csFileManager::Zippa( "$filename.zip", $strToWrite, 'gz' );
      }else
         $result = 1;


      return $result;
   }

   //--------------------------------------------------------------------------

   function TabViewer( $ItemsInfo, $titPage, $dbString='' ){
      $arrFind = array();

      if(count($ItemsInfo -> SqlBuilder -> ArrQuery)==0)
         $dbTable = $ItemsInfo -> SqlBuilder -> Table;

      if( strlen($dbString)>0 ){
         $dbString = csMlUser::DecryptDbString($dbString);
      }


      if( strlen($ItemsInfo -> FileTemplate)>0 ){
         $arrFind[0] = '<WP:Replace id="tbodys" />';
      }

      $arrReplaces = array();


      // Sql conditions
      $arrWhere = $ItemsInfo -> SqlBuilder -> ArrSimpleWhere;


      $qryTot = (isset($ItemsInfo -> SqlBuilder -> ArrQuery["Count"]))? $ItemsInfo -> SqlBuilder -> ArrQuery["Count"]:"SELECT count(*) FROM $dbTable";

      if( count($arrWhere)>0  )
         $qryTot .= " WHERE ". join(' AND ',$arrWhere);

      $conn = ($dbString!='')? dbTools::CreaConn($dbString) : null;
      $ItemsInfo -> TotaleItems = dbTools::EseguiQueryScalare( $qryTot, $conn );
      //unset($conn);



      if( $ItemsInfo -> TotaleItems > 0 ){

         // SQL per la paginazione del recordset
         $qryLimit = ( $ItemsInfo -> UsePagination() )? dbTools::SqlLimit( $ItemsInfo -> ItemsInPage, $ItemsInfo -> Offset ) : '';

         $sSort = (strlen($ItemsInfo -> SqlBuilder -> OrderBy)>0)? $ItemsInfo -> SqlBuilder -> OrderBy : '';


         $qry=(isset($ItemsInfo->SqlBuilder->ArrQuery["RecordSet"]))? $ItemsInfo->SqlBuilder->ArrQuery["Count"]:"SELECT * FROM $dbTable";

         if(count($arrWhere)>0) $qry .= " WHERE ". join(' AND ',$arrWhere);
         if(strlen($sSort)>0)  $qry .= " ORDER BY $sSort";

         $qry .= $qryLimit;

         //echo $qry;

         $sql = new sql_inject();
         if($sql->test($qry)){
            $dir = '';
            $relative_url = "errorPage.php";
            $qsParam = array( "error" => "004", "pageError" => urlencode($_SERVER['PHP_SELF']) );
            myTools::Redirect( $_SERVER["HTTP_HOST"], $dir, $relative_url, $qsParam);
         }

         $conn = ($dbString!='')? dbTools::CreaConn($dbString) : null;
         $res = dbTools::GetRecordSet( $qry, $conn );
         //unset($conn);

         $sThead = '';
         for($j=0;$j<$res->getNumCols();$j++){
            $col = $res->getColName($j);
            $sThead .= '<th>'.$col;

            if(in_array($col, $ItemsInfo->SqlBuilder->ArrSortColumn)){
               $sLink = $_SERVER["SCRIPT_NAME"]. myTools::BuildQueryString( array('tab' => $dbTable, 'sort_col'=>$col, 'sort_mode'=>'ASC'), true, true );
               $sThead .= '<a href="'.$sLink.'"><img src="'.myTools::ResolveUrl(array('templates',$GLOBALS["TEMPLATE"],'img','tools'), 'orderAsc.gif', false).'" width="15" height="15" alt="Sort '.$col.' ASC" border="0" /></a>';
               $sThead .= '&nbsp;';
               $sLink = $_SERVER["SCRIPT_NAME"]. myTools::BuildQueryString( array('tab' => $dbTable, 'sort_col'=>$col, 'sort_mode'=>'DESC'), true, true );
               $sThead .= '<a href="'.$sLink.'"><img src="'.myTools::ResolveUrl(array('templates',$GLOBALS["TEMPLATE"],'img','tools'), 'orderDesc.gif', false).'" width="15" height="15" alt="Sort '.$col.' DESC" border="0" /></a>';
            }

            $sThead .= '</th>';
         }
         $sThead = '
            <thead>
               <tr>
                  '.$sThead.'
               </tr>
            </thead>';

         $sTfoot = '
            <tfoot>
               <tr>
                  <td colspan="'.$res->getNumCols().'">'.date( FORMATO_DATA ).'</td>
               </tr>
            </tfoot>';

         $arrBody = array();
         $arrColLink = $ItemsInfo->SqlBuilder->ArrColumnLink;

         for( $i=0; $i<$res->getNumRows(); $i++ ){
            $myRow=array();
            for($j=0;$j<$res->getNumCols();$j++){
               $value = $res->getColumn_by_num($i,$j);
               $colName = $res->getColName($j);

               // formatto le colonne con tipo data
               if(strpos($colName, "dt_", 0) !== false && strlen($value)>0)
                  $value = myTools::Format('Date', $value);

               if(isset($arrColLink[$colName])){
                  if(isset($arrColLink[$colName]["JsRemotingControlID"]) && isset($arrColLink[$colName]["JsRemotingFun"]) ){
                     $jsParams = array();
                     $jsParams[] = "'".$arrColLink[$colName]["JsRemotingControlID"]."'";
                     $jsParams[] = "'".$arrColLink[$colName]["Tab"]."'";
                     $jsParams[] = "'".$arrColLink[$colName]["Col"]."'";
                     $jsParams[] = "'".$value."'";
                     $sJsParams = implode(',',$jsParams);
                     $value = '<a href="javascript:;" onclick="'.$arrColLink[$colName]["JsRemotingFun"].'('.$sJsParams.')">'.$value.'</a>';
                  }else{
                     $qs = myTools::BuildQueryString( array('tab' => $arrColLink[$colName]["Tab"], 'cols'=>$arrColLink[$colName]["Col"], 'vals'=>$value, 'action'=>'recviewer'  ), true, true );
                     $value = '<a href="'.$_SERVER["SCRIPT_NAME"].$qs.'" rel="external">'.$value.'</a>';
                  }

               }

               $cssClass   = (bcmod( $i, 2 ) == "0")? "P":"D";
               $myRow[] = "<td class=\"row$cssClass\">$value</td>";  //\n\t\t\t\t\t\t\t
            }

            $arrBody[] = "<tr>".implode('', $myRow)."</tr>\n"; //\t\t\t\t\t\t
         }

         $sBody='
            <tbody>
               '."\n".implode('', $arrBody).'
            </tbody>';
         array_push( $arrReplaces, $sBody );
      }else
         array_push( $arrReplaces, 'Non vi sono record per la query richiesta' );


       $ItemsInfo -> ArrFind               = $arrFind;
       $ItemsInfo -> ArrReplaces            = $arrReplaces;

      // Global Replace
      $arrGlobalFind = array();
      $arrGlobalFind[0] = '<WP:GlobalReplace id="thead" />';
      $arrGlobalFind[1] = '<WP:GlobalReplace id="tfoot" />';
      $arrGlobalFind[2] = '<WP:GlobalReplace id="TitPagina" />';

      $arrGlobalReplaces = array();
      $arrGlobalReplaces[0] = $sThead;
      $arrGlobalReplaces[1] = $sTfoot;
      $arrGlobalReplaces[2] = $titPage;
      $ItemsInfo -> ArrGlobalFind         = $arrGlobalFind;
      $ItemsInfo -> ArrGlobalReplaces     = $arrGlobalReplaces;

      unset($conn);

      return $ItemsInfo;
   }

   function DbSupportTransaction(){
      if( DB_TYPE == 1 ) return true;
      else if( DB_TYPE == 2 && mysql_get_server_info()>=4  )  return true;

       return false;
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
	
	// ---------------------------------------------------------------


	//-------------------------------------------------------------------------
	
	//-------------------------------------------------------------------------
	// METODI REPLACE

   function replaceEntity($str){

      //----------------------------------------------
      //
      // Per funzionare si deve usare questo META TAG:
      // <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">
      //
      //----------------------------------------------



//      if( !ereg("(/>/\r\n|\n|\r)" ,$str) )
//         $str=ereg_replace("(\r\n|\n|\r)", "<br />",$str);
//      else
//         $str=ereg_replace("(\r\n|\n|\r)", "<br />",$str);

      if( !ereg("(/>/\r\n|\n|\r)" ,$str) )
         $str=ereg_replace("(\r\n|\n|\r)", "<br />",$str);

      $str=preg_replace("/&(?![a-zA-Z]{2,6};|#[0-9]{3};)/i","&amp;",$str);

      $str=str_replace ("à","&agrave;",$str);
      $str=str_replace ("è","&egrave;",$str);
      $str=str_replace ("ì","&igrave;",$str);
      $str=str_replace ("ò","&ograve;",$str);
      $str=str_replace ("ù","&ugrave;",$str);

      $str=str_replace ("á","&aacute;",$str);
      $str=str_replace ("é","&eacute;",$str);
      $str=str_replace ("í","&iacute;",$str);
      $str=str_replace ("ó","&oacute;",$str);
      $str=str_replace ("ú","&uacute;",$str);

      $str=str_replace ("À","&Agrave;",$str);
      $str=str_replace ("È","&Egrave;",$str);
      $str=str_replace ("Ì","&Igrave;",$str);
      $str=str_replace ("Ò","&Ograve;",$str);
      $str=str_replace ("Ù","&Ugrave;",$str);

      $str=str_replace ("Á","&Aacute;",$str);
      $str=str_replace ("É","&Eacute;",$str);
      $str=str_replace ("Í","&Iacute;",$str);
      $str=str_replace ("Ó","&Oacute;",$str);
      $str=str_replace ("Ú","&Uacute;",$str);

      $str=str_replace ("ñ","&ntilde;",$str);
      $str=str_replace ("Ñ","&Ntilde;",$str);

      $str=str_replace ("ä","&auml;",$str);
      $str=str_replace ("Ä","&Auml;",$str);
      $str=str_replace ("ë","&euml;",$str);
      $str=str_replace ("Ë","&Euml;",$str);
      $str=str_replace ("ï","&iuml;",$str);
      $str=str_replace ("Ï","&Iuml;",$str);
      $str=str_replace ("ö","&ouml;",$str);
      $str=str_replace ("Ö","&Ouml;",$str);
      $str=str_replace ("ü","&uuml;",$str);
      $str=str_replace ("Ü","&Uuml;",$str);

      $str=str_replace ("â","&acirc;",$str);
      $str=str_replace ("Â","&Acirc;",$str);
      $str=str_replace ("ê","&ecirc;",$str);
      $str=str_replace ("Ê","&Ecirc;",$str);
      $str=str_replace ("î","&icirc;",$str);
      $str=str_replace ("Î","&Icirc;",$str);
      $str=str_replace ("ô","&ocirc;",$str);
      $str=str_replace ("Ô","&Ocirc;",$str);
      $str=str_replace ("û","&ucirc;",$str);
      $str=str_replace ("Û","&Ucirc;",$str);

      $str=str_replace ("ç","&ccedil;",$str);
      $str=str_replace ("Ç","&Ccedil;",$str);

      $str=str_replace ("ß","&szlig;",$str);

      $str=str_replace ("€","&euro;",$str);

      $str=str_replace ("°","&deg;",$str);


      //$str=str_replace ("'","&acute;",$str);

      //$str=str_replace ('"',"&quot;",$str);

      //uso anche questo replace perchè mi mette un backslash per l'apostrofo.. bohhhh
      //$str=str_replace ("\\","",$str);

      return $str;
   }


   // ---------------------------------------------------------------

	function replace4DB($str){

      $str = dbTools::replaceEntity($str);

		$str=ereg_replace("(\r\n|\n|\r|\t)", "",$str );

      if (!get_magic_quotes_gpc())
		   $str = addslashes($str); 

		return $str;
	}

	// ---------------------------------------------------------------

	function replace4SqlDB($str){
		$str=str_replace ("'","''",$str);

		return $str;
	}

   // ---------------------------------------------------------------

   function replace4OutputStrip($str){

      $str = str_replace ("\'", "'", $str);
      $str = str_replace ('\"', '"', $str);

      return $str;
   }

   // ---------------------------------------------------------------
		
	function replaceDB4Text($str){

		// replace from DB for TextArea

		//$str = ereg_replace( "<br( /)?
      //>", "\n", $str);
		
		$str=str_replace ("&agrave;","à",$str);
		$str=str_replace ("&egrave;","è",$str);
		$str=str_replace ("&igrave;","ì",$str);
		$str=str_replace ("&ograve;","ò",$str);
		$str=str_replace ("&ugrave;","ù",$str);

		$str=str_replace ("&aacute;","á",$str);
		$str=str_replace ("&eacute;","é",$str);		
		$str=str_replace ("&iacute;","í",$str);	
		$str=str_replace ("&oacute;","ó",$str);
		$str=str_replace ("&uacute;","ú",$str);		
		
		$str=str_replace ("&Agrave;","À",$str);
		$str=str_replace ("&Egrave;","È",$str);
		$str=str_replace ("&Igrave;","Ì",$str);
		$str=str_replace ("&Ograve;","Ò",$str);
		$str=str_replace ("&Ugrave;","Ù",$str);

		$str=str_replace ("&Aacute;","Á",$str);
		$str=str_replace ("&Eacute;","É",$str);
		$str=str_replace ("&Iacute;","Í",$str);
		$str=str_replace ("&Oacute;","Ó",$str);
		$str=str_replace ("&Uacute;","Ú",$str);		
		
		$str=str_replace ("&ntilde;","ñ",$str);
		$str=str_replace ("&Ntilde;","Ñ",$str);
		
		$str=str_replace ("&auml;","ä",$str);
		$str=str_replace ("&Auml;","Ä",$str);
		$str=str_replace ("&euml;","ë",$str);
		$str=str_replace ("&Euml;","Ë",$str);
		$str=str_replace ("&iuml;","ï",$str);
		$str=str_replace ("&Iuml;","Ï",$str);
		$str=str_replace ("&ouml;","ö",$str);
		$str=str_replace ("&Ouml;","Ö",$str);
		$str=str_replace ("&uuml;","ü",$str);
		$str=str_replace ("&Uuml;","Ü",$str);
		
		$str=str_replace ("&acirc;","â",$str);
		$str=str_replace ("&Acirc;","Â",$str);
		$str=str_replace ("&ecirc;","ê",$str);
		$str=str_replace ("&Ecirc;","Ê",$str);
		$str=str_replace ("&icirc;","î",$str);
		$str=str_replace ("&Icirc;","Î",$str);
		$str=str_replace ("&ocirc;","ô",$str);
		$str=str_replace ("&Ocirc;","Ô",$str);
		$str=str_replace ("&ucirc;","û",$str);
		$str=str_replace ("&Ucirc;","Û",$str);

		$str=str_replace ("&ccedil;","ç",$str);
		$str=str_replace ("&Ccedil;","Ç",$str);
		$str=str_replace ("&szlig;","ß",$str);

        $str=str_replace ("&euro;","€",$str);
		
		$str=str_replace ("&acute;","'",$str);

        $str=str_replace ("&quot;",'"',$str);

		$str=str_replace ("&deg;","°",$str);

        $str=str_replace ("&amp;","&",$str);

        //$str = stripslashes($str);
        $str = dbTools::replace4OutputStrip($str);


		return $str;
	}

	// ---------------------------------------------------------------

	function replace4Code($str){

		// replace per creare stringhe di nome codice
		// elimina tutti i caratteri che non siano lettere (alpha) o numeri - [^[:alnum:]]
		$str=preg_replace("/[^a-z0-9\-_]/i", "", $str);



		return $str;
	}		
	
	//-------------------------------------------------------------------------

}



   /*
   function ShowTable( $Tab, $StrWhere="", $StrOrderBy="", $strLinkOrderBy='', $postPage='', $cols='' ){

      if(strpos($Tab,"SELECT") !== false)
         $qryShow = $Tab;
      else{

      $qryShow = "SELECT ";

      $qryShow .= (strlen($cols)>0)? $cols : "*";

      $qryShow .= " FROM $Tab" . $StrOrderBy . $StrWhere;
      }

      $res = dbTools::GetRecordSet($qryShow);

      //echo "<div>".$qryShow."</div>";


      $newRecordHeader ="\n\n<table class=\"tabViste\" border='1' cellpadding='2' cellspacing='2'>\n";
      $newRecordFooter ="</table>\n";

      $tabHeader="\t<tr>\n";

      // creo Array con colonne orderby
      $arrSortColumns = array();
      if( $strLinkOrderBy != '' )
         $arrSortColumns = split( ';', $strLinkOrderBy );

      for($i=0;$i<$res->getNumCols();$i++){

         if( count($arrSortColumns) > 0 ){

            $tabHeaderTmp = '';
            for($j=0;$j<count($arrSortColumns);$j++){

               if( $res->getColName($i) == $arrSortColumns[$j] ){
                  $tabHeaderTmp = "\t\t<td class=\"tabViste\" bgcolor=\"#9a9a9a\" nowrap><span style=\"color: #003366; font-weight: bold; text-align: center; text-transform: uppercase;\">";
                  $tabHeaderTmp .= $res->getColName($i);
                  $tabHeaderTmp .= " <a href=\"". $postPage ."?oTab=". $Tab ."&amp;orderby=".$arrSortColumns[$j].":ASC\"><IMG src=\"/img/tools/orderAsc.gif\" width=\"15\" height=\"15\" border=\"\" alt=\"Ordina Crescente\"></a>";
                  $tabHeaderTmp .= " <a href=\"". $postPage ."?oTab=". $Tab ."&amp;orderby=".$arrSortColumns[$j].":DESC\"><IMG src=\"/img/tools/orderDesc.gif\" width=\"15\" height=\"15\" border=\"\" alt=\"Ordina Decrescente\"></a>";
                  $tabHeaderTmp .= "</span></td>\n";
               }

            }

            if( $tabHeaderTmp != '' )
               $tabHeader.= $tabHeaderTmp;
            else
               $tabHeader.="\t\t<td class=\"tabViste\" bgcolor=\"#9a9a9a\"><span style=\"color: #003366; font-weight: bold; text-align: center; text-transform: uppercase;\">".$res->getColName($i)."</span></td>\n";


         }else
            $tabHeader.="\t\t<td class=\"tabViste\" bgcolor=\"#9a9a9a\"><span style=\"color: #003366; font-weight: bold; text-align: center; text-transform: uppercase;\">".$res->getColName($i)."</span></td>\n";


      }

      $tabHeader.="\t</tr>\n";



      $numeroCol=$res->getNumCols();

      $newRecord="";
      for($i=0;$i<$res->getNumRows();$i++){
         $newRecord.="\t<tr>\n";
         for($ii=0;$ii<$numeroCol;$ii++){

            $v = $res->getColumn_by_num($i,$ii);
            $v = (strlen($v)>0)? $v : "-";

            if( strpos( $res->getColName($ii), "id_") === false )
               $newRecord.="\t\t<td class=\"tabViste\" valign='top'>$v</td>\n";
            else{

               $tabella = '';

               switch( $res->getColName($ii) ){
                  case "id_prenotazione":
                     $tabella = "prenotazioni";
                  break;

                  case "id_utente":
                     $tabella = "utenti";
                  break;

                  case "id_ecard":
                     $tabella = "ecards";
                  break;

                  case "id_amico":
                     $tabella = "amici";
                  break;
               }

               if($tabella == "ecards")
                  $newRecord .= '
                     <td class="tabViste" valign="top">
                        '. $v .'</td>
                  ';
               else
                  $newRecord .= '
                     <td class="tabViste" valign="top">
                        <a href="'.myTools::ResolveUrl(array( TOOLS_DIRECTORY,'viste'), 'visRecord.php', false).'?oTab='. $tabella .'&'. $res->getColName($ii) .'='. $v .'">'. $v .'</a></td>
                  ';
            }

         }
         $newRecord.="\t</tr>\n";
      }
      $newRecord=$newRecordHeader.$tabHeader.$newRecord.$newRecordFooter;

      return $newRecord;
   }
   */


?>