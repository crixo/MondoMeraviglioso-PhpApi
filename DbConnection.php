<?
//-------------------------------------------------------
//     DATABASE.INDEPENDENCE.OBJECT.WRAPPER
//     VERSION 0.40
//     VERSION DATE: 4th July 2000
//-------------------------------------------------------
//     Author: Manik Surtani <manik@post1.com>
//	Modifiche: picchio@mbtlc.it
//		- numtuples
//		- errormsg
//		- multi db support: implementare multi db
//		  per aggiornare database remoti solo in
//		  update (quando numtuples > 0).
//		  Scopo e' quello di leggere i dati in locale ma di aggiornare anche i db 
//		  remoti.
//		Es:
//			$conn = new connection;
//			$resnum = new resultSet;
//			$conn->open($dbstring);
//			$conn->add_db("dbname=mbcont password='' user=picchio port=3452 host=localhost");
//			$conn->runSQL($qry); // Esegue in remoto se numtuple > 0
//			$conn->runActionQuery($qry); // Esegue comunque anche in remoto. (se riesce)
//			$conn->close();
//
//		- Esegue in automatico sequenza "BEGIN" ... "COMMIT" su tutti i db interessati
//		- Per fare "COMMIT" chiamare:
//			$conn->close("COMMIT"); // "COMMIT" e' il default
//		- Per fare  "ROLLBACK" (annullare transazioni)
//			$conn->close("ROLLBACK"); // "COMMIT" e' il default
//		- Se non si vuole BEGIN dopo creazione classe eseguire $conn->conn_begin_str = "";
//		Es:
//			$conn = new connection;
//			$conn->conn_begin_str = "";
//			$conn->close("");
//			
//-------------------------------------------------------


//-------------------------------------------------------
// V.0.40 : A bug fix was made to make this library
// compatible with php4 as well as php3.0.x.
//-------------------------------------------------------
// This version currently supports POSTGRESQL and MYSQL
// databases.  More support to be added sometime .... !!
//-------------------------------------------------------
// This wrapper provides 'physical' database connectivity
// and works as the link between the php3-based web site
// and the selected database.
//-------------------------------------------------------


// set the database TYPE.
//DB_TYPE==1 --> POSTGRES
//DB_TYPE==2 --> MYSQL



//$dbstring = "dbname=raftingdb password='7-!2' user=raftingw";

//$dbstring = "dbname=raftingdb password= user=root";
//$dbstring = "username=raftingw dbname=raftingdb password=raftingw";

//$conn = new connection;
//$conn -> open($dbstring);

/*

---------------------------------------------------------
"class resultSet" deals with a recordset produced by a
databse query.
---------------------------------------------------------

*/


class resultSet {
  var $element = array();
  var $column_name = array();
  var $numrows;
  var $numcols;
  var $numtuples; // Picchio's add.
  var $errormsg;  // Picchio's add.

function getColumn_by_name($rowNum,$colName) {
  $j=-1;
  for($i=0;$i<$this->numcols;$i++)
  {
   if ($this->column_name[$i]==$colName) { $j=$i; }
  }
  //echo "$j-$rowNum";
  if ($j!=-1) { return $this->element[$rowNum][$j];} else { return ""; }
}

function getColumn_by_num($rowNum,$colNum) {
  return $this->element[$rowNum][$colNum];
}

function getColName($column) {
   return $this->column_name[$column];
}

function getNumRows()  {
   return $this->numrows;
}

function getNumCols()  {
   return $this->numcols;
}
// Picchio aggiunta!
function getNumTuples()  { 
   return $this->numtuples; 
} 
function getErrorMsg()  {
   return $this->errormsg;
}

function convertToArray(){
	$arr = array();
	for( $r=0; $r<$this->getNumRows(); $r++ ){
		for( $c=0; $c<$this->getNumCols(); $c++ ){
			$arr[$r][$this->getColName($c)] = $this->getColumn_by_num( $r, $c);
		}
	}
	return $arr;
}


// Fine picchio add

   function getRow($rowNum) {
     return $this->element[$rowNum];
   }

   function HasColumn($colName){
      for($j=0;$j<$this->numcols;$j++){
         if($this->getColName($j)==$colName) return true;
      }
      return false;
   }
   
   function getRowByKey($kName,$kVal) {
      for($row=0;$row<$this->numrows;$row++){
         if($this->getColumn_by_name($row,$kName)==$kVal)
            return $this->element[$row];

      }
      return false;
   }
}



/*

---------------------------------------------------------
"class ConnInfo" provides a connection details object which returns information
given on a connection parameter to a database.
---------------------------------------------------------

*/


class ConnectionInfo {
  var $cdbname;
  var $cusername;
  var $cpassword;
  var $chost;
  var $cport;

function SplitThis($cparam) {
    $tok = strtok($cparam," ");
    while($tok) {
        $pos=strpos($tok,"=");
        $type=substr($tok,0,$pos);
        switch ($type) {
           case "dbname":
              $this->cdbname=substr($tok,$pos+1);
              break;
           case "host":
              $this->chost=substr($tok,$pos+1);
              break;
           case "port":
              $this->cport=substr($tok,$pos+1);
              break;
           case "username":
              $this->cusername=substr($tok,$pos+1);
              break;
           case "password":
              $this->cpassword=substr($tok,$pos+1);
              break;
        }
        $tok = strtok(" ");
    }
}

function dbName() { return $this->cdbname; }
function Host() { return $this->chost; }
function Port() { return $this->cport; }
function UserName() { return $this->cusername; }
function Password() { return $this->cpassword; }

}


/*

---------------------------------------------------------
"class connection" provides functionality to deal with the
actual database.
---------------------------------------------------------

*/



class connection {
  var $my_connection;
  var $my_temp_resultID;
  var $my_temp_result_object;
  /* Picchio's */
  var $conn_strgs = ""; // Array stringhe di connessione.
  var $conn_ids = ""; // Array con ID di connessione.
  var $conn_oks = FALSE; // Variabile ok se tutti connessi.
  var $conn_my_temp_resultID; // Array con risultati remoti
  var $conn_begin_str; // Stringa di connessione (Automatica a "BEGIN")
  /* Picchio's */

  /* WP INIT */
  var $IsTransaction;
  var $ConnectionInfo;
  var $IsClose=true;
  /* WP END */



	// ---------------------------------------------------------------
	// COSTRUTTORE
  function connection(){
    $this -> my_temp_result_object = new resultSet;
		/* Picchio's add */
			 $this->conn_oks = FALSE;
			 $this->my_temp_result_object->errormsg = "";
			 $this->conn_begin_str = "BEGIN";
		 //  $this->conn_strgs = "";
		/* Picchio's add */

      $this -> IsTransaction = false;
	}
	

	/*
		Picchio's add
	*/	
	// ---------------------------------------------------------------
	
	function add_db ($db_string) {

		/*
		* Aggiunge un elemento
		*/
		$this->conn_strgs[] = $db_string;
	}
	
	
	// ---------------------------------------------------------------	
	
	
	function test_add_db () {
		/*
		* Test stinghe
		*/
		if (!is_array($this->conn_strgs)) return 0;
		while (list($k,$v) = each($this->conn_strgs)) {
			print "$k -> $v <BR>";
		}
	}	
	
	
	// ---------------------------------------------------------------		
	
	
	function runSQL_remote ($someSQL) {
		/*
		* Esegue query remote
		*/
		if (!is_array($this->conn_ids)) return 0;
		reset($this->conn_ids);
		//print "<HR>Eseguo in remoto: <PRE>$someSQL</PRE>";
		while (list($k,$v) = each($this->conn_ids)) {
			$this->conn_my_temp_resultID[$k] = pg_exec($v, $someSQL);
			if ($this->conn_my_temp_resultID[$k] <> "") {
				// Sembra OK
			} else {
				$this->my_temp_result_object->errormsg .= 
					pg_errormessage($this->conn_my_temp_resultID[$k]);
				print "<HR>Errore esecuzione query remota $k " . 
					$this->my_temp_result_object->errormsg . 
					"<HR>" ;
			}
		}
	}
	
	
	
	// ---------------------------------------------------------------		
	
	
	function connect_add_db () {
		/*
		* Si connette ai db se non gia' connesso.
		* Imposta $this->conn_oks se tutto ok.
		*/
		$i = 0;
		$this->conn_oks = TRUE;
		if (!is_array($this->conn_strgs)) return 0;
		reset($this->conn_strgs);
		while (list($k,$v) = each($this->conn_strgs)) {
			//print " Connetto a:  $v <BR>";
			// Controllo errato: if (!isset($this->conn_ids[$i])) {
			if (1==1) {
				$this->conn_ids[$i] = pg_connect($v);
				if ($this->conn_ids[$i]) {
					if ($this->conn_begin_str <> "") {
						$this->conn_my_temp_resultID[$k] = pg_exec(
							$this->conn_ids[$i], $this->conn_begin_str);
					}
					// Ok. Connesso
				} else {
					// KO: Non connesso
					print "Errore connessione a: $v <BR>";
					$this->conn_oks = FALSE;
				}
			}
			$i++;
		}
	}
	
	/*
		Fine Picchio's add
	*/


	// ---------------------------------------------------------------
	
	
  function open($p1, $p2 = "", $p3 = "", $p4 = "", $p5="" )  {

		$ok = false;
      $connINF = new ConnectionInfo;

		if (($p2 == "") && ($p3 == "") && ($p4 == "") && ($p5 == "") && (DB_TYPE==1)){
			$this->my_connection=pg_connect($p1);
			
			// picchio: Aggiunto controllo 
			if ($this->my_connection) {
				if ($this->conn_begin_str <> "") {
					$this->my_temp_resultID = pg_exec($this->my_connection, $this->conn_begin_str);
				}
				$ok = true;
			} else {
				//$this->my_temp_result_object->errormsg = pg_errormessage($this->my_connection);
				$ok = false;
			}
		}

		if (($p5 == "") && (DB_TYPE==2)){

			 $connINF->SplitThis($p1);
			 $p1=$connINF->Host();
			 $p2=$connINF->UserName();
			 $p3=$connINF->Password();
			 $p4=$connINF->dbName();



          $this->my_connection=mysql_connect($p1, $p2, $p3);

          mysql_select_db($p4,$this->my_connection);

          // Funziona con mySql 4.0 e con le table type = innoDB
          if( $this -> IsTransaction )
            mysql_query("BEGIN", $this->my_connection);

					$ok = true;
		}

		if (!($ok)){
			print "\n\nTHERE WAS AN ERROR CONNECTING.\n\n";
		}

      $this -> ConnectionInfo = $connINF;

      $this -> IsClose = false;
		
		return $ok;
  }
  
	
	// ---------------------------------------------------------------  


	function close($close_command = "COMMIT") {



		if (DB_TYPE==1) {
			if ($close_command <> "") {
				pg_exec($this->my_connection, $close_command);
				if($this->conn_oks)
					$this->runSQL_remote($close_command);
			}
			
			pg_close($this->my_connection);
			

			/* Picchio's add*/
			if ($this->conn_oks) {
				if (!is_array($this->conn_ids))
					return 0;
					
				reset($this->conn_ids);
				
				while (list($k,$v) = each($this->conn_ids)) {
					pg_close($v);
				}
			}
			/* Picchio's add*/	
		}
		

		if (DB_TYPE==2){

         if($this -> IsTransaction)
            mysql_query($close_command, $this->my_connection);

			mysql_close($this->my_connection);
		}

      $this -> IsClose = true; 
	}


	// --------------------------------------------------------------- 
	

	function runActionQuery($someSQL){


		// cleanup SQL for PHP versions! (sic)
		if (substr($someSQL,strlen($someSQL)-1,1)==";")
			 $someSQL=substr($someSQL,0,strlen($someSQL)-1);
			 
		if (DB_TYPE==1) {
			//pg_exec($this->my_connection, $someSQL);

			/* Picchio's add*/
			$this->my_temp_resultID = pg_exec($this->my_connection, $someSQL);
			
			if ($this->my_temp_resultID <> "")
				$this->my_temp_result_object->numtuples = pg_cmdtuples($this->my_temp_resultID);
			else
				$this->my_temp_result_object->numtuples = -1;

			if (!$this->conn_oks) 
				$this->connect_add_db();

			if ($this->conn_oks)
				$this->runSQL_remote($someSQL);
			else
				print "<HR>ERRORE Connessione db remoti.<HR>";
			/* Picchio's add*/

			$this->my_temp_result_object->errormsg .= pg_errormessage($this->my_connection);

		}

		if (DB_TYPE==2)
			$this->my_temp_result_object->errormsg .= (mysql_query($someSQL, $this->my_connection))? '':mysql_error();



		
		return $this->my_temp_result_object->errormsg;
	}


	// --------------------------------------------------------------- 
	

	function runSQL($someSQL){
      //echo "<pre>$someSQL</pre>";

		// cleanup SQL for PHP versions! (sic)
		if (substr($someSQL,strlen($someSQL)-1,1)==";")
					$someSQL=substr($someSQL,0,strlen($someSQL)-1);

    if (DB_TYPE==1){
    	
			// picchio's modifica
			//print "<PRE>$someSQL</PRE>";
			$this->my_temp_resultID = pg_exec($this->my_connection, $someSQL);
			
			if ($this->my_temp_resultID <> "") {
				$this->my_temp_result_object->numrows = pg_numrows($this->my_temp_resultID);
				$this->my_temp_result_object->numcols = pg_numfields($this->my_temp_resultID);
				// Tuples
				$this->my_temp_result_object->numtuples = pg_cmdtuples($this->my_temp_resultID);
			} else {
				$this->my_temp_result_object->numrows = -1;
				$this->my_temp_result_object->numcols = -1;
				$this->my_temp_result_object->numtuples = -1;
			}
			
			if ($this->my_temp_result_object->numtuples > 0) {
				// Eseguo query aggiornamento su db remoti.
				if (!$this->conn_oks) $this->connect_add_db();
				
				if ($this->conn_oks)
					$this->runSQL_remote($someSQL);
				else
					print "<HR>ERRORE Connessione db remoti.<HR>";
					
			}
		
			
			$this->my_temp_result_object->errormsg .= pg_errormessage($this->my_connection);		
			// Fine picchio's modifica
		
       // fill column_names from resultset
       for ($j=0; $j < $this->my_temp_result_object->numcols; $j++)
       	$this->my_temp_result_object->column_name[$j] = pg_fieldname($this->my_temp_resultID, $j);

       // fill data elements from resultset
       for ($i=0; $i < $this->my_temp_result_object->numrows; $i++){
       	for ($j=0; $j < $this->my_temp_result_object->numcols; $j++){
        	$this->my_temp_result_object->element[$i][$j] = pg_result($this->my_temp_resultID, $i, $j);
        }
       }


			//////////////////////////////////////////////
			// WP DEBUG  && strpos($_SERVER["PHP_SELF"], "toolsPHP") > 0 
			//////////////////////////////////////////////
			if( $this->my_temp_result_object->errormsg != '' && DEBUG)
				echo "<div>Errore:<br><pre>". $someSQL ."</pre><br>".$this->my_temp_result_object->errormsg."</div>";        
					

      return $this->my_temp_result_object;

      pg_freeresult($this->my_temp_resultID);
		}


		if (DB_TYPE==2){
			$this->my_temp_resultID = mysql_query($someSQL, $this->my_connection);
         //error_log ("Test:".ereg_replace("(\r\n|\n|\r|\t| {2,})", "", $someSQL ), 0);

			$this->my_temp_result_object->numrows = mysql_num_rows($this->my_temp_resultID);
			$this->my_temp_result_object->numcols = mysql_num_fields($this->my_temp_resultID);

      // fill column_names from resultset
			for ($j=0; $j < $this->my_temp_result_object->numcols; $j++)
				$this->my_temp_result_object->column_name[$j] = mysql_fieldname($this->my_temp_resultID, $j);


      // fill data elements from resultset
			for ($i=0; $i < $this->my_temp_result_object->numrows; $i++){
				$x = mysql_fetch_row($this->my_temp_resultID);
				for ($j=0; $j < $this->my_temp_result_object->numcols; $j++)
					$this->my_temp_result_object->element[$i][$j] = $x[$j];
			}
			
			if( mysql_errno() > 0 )
				$this -> my_temp_result_object->errormsg .= "<div>".mysql_errno() . ": " . mysql_error()."</div>\n";			

			//////////////////////////////////////////////
			// WP DEBUG  && strpos($_SERVER["PHP_SELF"], "toolsPHP") > 0 
			//////////////////////////////////////////////
			if( $this->my_temp_result_object->errormsg != '' && DEBUG)
				echo "<div>Errore:<br><pre>". $someSQL ."</pre><br>".$this->my_temp_result_object->errormsg."</div>";        


      return $this->my_temp_result_object;
      
    	mysql_free_result($this->my_temp_resultID);
		}

	}

}




?>
