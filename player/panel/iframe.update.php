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


			$downloadStart = false;
			echo str_repeat('', 4096) . "\n"; send();

			// Required functions
			function send () { ob_flush(); flush(); }
			function js ($txt) { echo "<script>{$txt}</script>"; send(); }


			// Log errors into file
			error_reporting(E_ALL ^ E_NOTICE);
			ini_set('default_charset', 'utf-8');
			ini_set("log_errors", "on");
			ini_set("error_log", getcwd() . "./../tmp/logs/php.log");
			ignore_user_abort(true);
			set_time_limit(0);

			// Include stuff
			include './../inc/functions.php';
			if ( is_file('./../inc/conf/general.php') ) include './../inc/conf/general.php';


			// Kill output buffers
			while (ob_get_level()) { ob_end_flush(); }


			// Start new one
			ob_implicit_flush(true);


			// Now lets get to bussines!
			if ( empty($settings['envato_pkey']) ) { // No envato key

				js('$(window.parent.document).find(".update-text").after(\'' . alert('Envato Purchase key is missing, unable to continue update process...', error) . '\');');	

			} else { // Key present


				// Check for lockfile, if doesn't exist create it, make sure update is not run twice
				if ( !is_file('./../tmp/updates/lock') ) {

					file_put_contents('./../tmp/updates/lock', '');

				} else if ( !class_exists('ZipArchive') ) { // Show error if ZipArchive is not supported

					js('$(window.parent.document).find(".update-text").after(\'' . alert('Update failed, unable to initiate ZipArchive class (ZIP Extension), please contact web hosting provider...', error) . '\');');	

				} else {

					js('$(window.parent.document).find(".update-text").html(\'<div class="text-red">Unable to start update because update is already in progress!</div>\');');

				}


				// First message
				js ('$(window.parent.document).find(".update-text").html(\'Connecting to the update server...\');');

				// Attempt download
				$data = get(
					'https://prahec.com/envato/update?action=get', "purchase-key={$settings['envato_pkey']}", 
					null,
					function ($res, $dtotal, $dnow, $utotal, $unow = '') {

						global $downloadStart;

						if ($downloadStart === false) {
							js('$(window.parent.document).find(".update-text").append(\'<div>Downloading update... (<span class="progress-status download">0%</span>)</div>\');');
							$downloadStart = true;
						}

						// PHP 5.3 fix
						if (is_resource($res)) { $total = $dtotal; $now = $dnow; } else { $total = $res; $now = $dtotal; }	

						// Calculate progress
						if ($total < 0) {

							$progress = 0;

						} else if ( $now >= 1 AND $total >= 1 ) { // Fix "Division by Zero"

							$progress = floor(($now / $total) * 100);

							echo '<script>
							$(window.parent.document).find(".progress-status.download").html(\'' . $progress . '%\');
							</script>';
							send();
						}

					}, 0);

				// Handle update server errors
				if ( $data === false ) {

					js('$(window.parent.document).find(".update-text").append(\'<div class="text-red">Connection to the update server failed!</div>\');');

				} else if (strpos($data, '"error"') !== false) {

					$json = json_decode($data, true);
					js('$(window.parent.document).find(".update-text").append(\'<div class="text-red">' . $json['error'] . '</div>\');');

				} else if ( file_put_contents('./../tmp/updates/update.zip', $data) === false ) {

					js('$(window.parent.document).find(".update-text").append(\'<div class="text-red">Saving update file failed, it seems that directory <b>/tmp/update/</b> is not writable!</div>\');');

				} else {

					// Extract etc...
					js('$(window.parent.document).find(".update-text").append(\'<div>Installing the latest update... (<span class="progress-status unzip">0/0</span>)</div>\');');

					// Initiate extract
					$zip = new ZipArchive;
					$files = $zip->open('./../tmp/updates/update.zip');					// Open update zip
					$path = realpath('./../');											// Where to extract update

					if ($files !== true) {

						js('$(window.parent.document).find(".update-text").append(\'<div class="text-red">Unable to read downloaded update file!</div>\');');

					} else {

						$total = $zip->numFiles;
						for($i = 0; $i < $total; $i++) {

							$tmp = $zip->getNameIndex($i);
							$zip->extractTo($path, array($tmp));

							$file = $i+1;
							js('$(window.parent.document).find(".progress-status.unzip").html(\'' . $file . '/' . $total . '\');');

						}

						$zip->close();

						// If update has some big changes it will include post install script to fix problems
						if (file_exists($path . '/update.postinstall.php')) {
							include $path . '/update.postinstall.php';
						}

						// Delete zip file & temp file
						@unlink('./../tmp/updates/update.zip');
						@unlink('./../tmp/updates/lock');

					}


					// Finished
					js('$(window.parent.document).find(".update-text").append(\'<div>Update completed successfully!</div>\');');
					js('$(window.parent.document).find(".update-text").after(\'' . alert('Update completed!<br>To view change log or updated content, please reload page.', success) . '\');');

				}

			}
		?>
	</body>
</html>