<?php

	// Log errors into file
	error_reporting(E_ALL ^ E_NOTICE);
	ini_set("log_errors", "on");
	ini_set("error_log", getcwd() . "./../tmp/logs/php.log");

	// Variables & functions
	$inc 				= true;
	$item 				= 'i2cjx7cx';		## Updates etc... Item ID to destingiush
	
	// Output buffer & PHP SESSION
	ob_start();
	session_start();

	// Required control panel files
	include 'template.php';
	include './../inc/functions.php';
	include './../inc/lib/forms.class.php';
	include './../inc/lib/image-resize.class.php';

	// Include general player settings
	if ( is_file('./../inc/conf/general.php') ) include './../inc/conf/general.php';

	
	// Logout user
	if ( isset($_GET['logout']) ) {
		unset($_SESSION['a-login']);
		header("Location: ?s=login");
	}


	head($settings);
	if ( $_SESSION['a-login'] !== true) {

		include 'login.php';

	} else {

		echo tabs();
		if ( is_file("{$_GET['s']}.php") ) {

			include "{$_GET['s']}.php";

		} else {

			include 'home.php';

		}



	}
	
	footer();

?>