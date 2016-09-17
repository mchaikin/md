<?php
	$db_host = '192.168.1.40';
	$db_name = 'radio';
	$db_username = 'root';
	$db_password = 'karuba';
	$db_table_to_show = 'radio';
	$connect_to_db = mysql_connect($db_host, $db_username, $db_password)
	or die("Could not connect: " . mysql_error());
	mysql_select_db($db_name, $connect_to_db)
	or die("Could not select DB: " . mysql_error());
?>
