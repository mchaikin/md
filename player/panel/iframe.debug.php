<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>AIO - Radio Player Update</title>
		<link rel="shortcut icon" href="favicon.ico">
		<script src="./../assets/js/jquery-1.11.2.min.js"></script>
	</head>
	<body>
		<?php

			// Make sure admin is doing update!
			session_start();
			if ( $_SESSION['a-login'] !== true) { exit(); }
			session_write_close();
			// It is admin ;)


			// Required functions
			function send () { ob_flush(); flush(); }
			function js ($txt) { echo "<script>{$txt}</script>"; send(); }


			// Log errors into file
			error_reporting(E_ALL ^ E_NOTICE);
			ini_set('default_charset', 'utf-8');
			ini_set("log_errors", "on");
			ini_set("error_log", getcwd() . "./../tmp/logs/php.log");
			set_time_limit(0);

			// Include stuff
			include './../inc/functions.php';
			if ( is_file('./../inc/conf/general.php') ) include './../inc/conf/general.php';

			// Load channels file (used for all actions on this page)
			if ( is_file("./../inc/conf/channels.php") ) { include ("./../inc/conf/channels.php"); }
			if ( !is_array($channels) ) $channels = array();


			// Kill output buffers
			echo str_repeat('', 4096) . "\n"; send();
			while (ob_get_level()) { ob_end_flush(); }


			// Start new one
			ob_implicit_flush(true);


			switch ($_GET['test']) {

				// Test port 8000 (Icecast/Shoutcast)
				case 'ports': $test = array( 'Shoutcast/Icecast' => 'http://defikon.com:8000/status.xsl' );
					break;

					// Centovacast test
				case 'centovacast':	$test = array( 'Centova Cast' => 'http://sc2.streamingpulse.com:2199/' );
					break;

					// Test port 80 (Radionomy)
				case 'radionomy': $test = array( 'Radionomy' => 'http://api.radionomy.com/currentsong.cfm' );
					break;

					// Test connection for all configured channels
				case 'user':

					foreach ( $channels as $channel ) {
						
						// Direct, Disabled and Radionomy are skipped
						if ( $channel['stats']['method'] == 'disabled' OR $channel['stats']['method'] == 'radionomy' ) {
							continue;
						}
						
						// Add channel to array
						$test[$channel['name']] = $channel['stats']['url'];
						
					}

					break;

				default: $test = array(
						'Shoutcast/Icecast' => 'http://defikon.com:8000/status.xsl', 
						'Centova Cast' => 'http://sc2.streamingpulse.com:2199/',
						'Radionomy' => 'http://api.radionomy.com/currentsong.cfm',
					);
					break;



			}


			// Now do the testing
			if ( is_array($test) AND count($test) >= 1 ) {

				$c = 1;
				
				// LOOP
				foreach ( $test as $name => $url ) {

					js ('$(window.parent.document).find(".debug-output").' . (( $c == 1 ) ? 'html' : 'append') . '(\'<b>' . $name . '</b>: Connecting to ' . $url . '...\');');

					// Test connection
					if ( get( parseURL($url) ) ) {

						js ('$(window.parent.document).find(".debug-output").append(\' <b><span style="color: green;">success!</span></b><br>\');');

					} else {

						js ('$(window.parent.document).find(".debug-output").append(\' <b><span style="color: red;">failed!</span></b><br>\');');

					}
					
					$c++;
				}
				// END LOOP

			} else {
				
				// Nothing to test, show "nothing to test" message
				js ('$(window.parent.document).find(".debug-output").html(\'Unable to find a channel for testing. Note: If you have single Radionomy channel, this does not work.\');');
				
			}
		?>
	</body>
</html>