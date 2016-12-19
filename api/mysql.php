<?php
	require('config_srv.php');
    require('config_app.php');

	$link = null;

	function sqlConnect(){
		global $link;
		
		$link = mysql_connect(DB_HOST,  DB_USER, DB_PASSWORD);

		if(! $link){
			fatal_error("Error mysql_connect()");
		}
		if(! mysql_select_db(DB_NAME, $link)){
			fatal_error("Error mysql_select_db()");
		}
	};

	function sqlClose(){
		global $link;
		if($link != null){
			mysql_close($link);
			$link = null;
		}
	}

	function sqlUpdate($query){
		global $link;
		if($link == null){
			sqlConnect();
		}
		if (!mysql_query($query, $link)) {
			fatal_error("Error mysql_query($query)");
		}
	}

	function sqlQuery($query){
		global $link;
		if($link == null){
			sqlConnect();
		}
		$array = array();
		if($res = mysql_query($query, $link)){
			while($row = mysql_fetch_assoc($res)){
				$array[] = $row;
			}
		}

		return $array;
	}

	function getRealEscapeString($string){
		global $link;

		if($link == null){
			sqlConnect();
		}

		return mysql_real_escape_string($string, $link);
	}
?>