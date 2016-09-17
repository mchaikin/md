<?php

	/* Function to display header of admin pages
	================================================================================== */
	function head ( $settings = array() ) {

	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title><?php echo (( empty($settings['title']) ) ? 'AIO - Radio Player' : $settings['title']); ?> :: Control Panel</title>
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="icon" type="image/png" href="favicon.png" sizes="32x32" />

		<link rel="stylesheet" href="//cdn.prahec.com/assets/css/style.css" type="text/css">
		<link href="//fonts.googleapis.com/css?family=Roboto:300,400,500,600" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="//cdn.prahec.com/assets/icons/awesome.css">

		<!-- AIO Radio Control Panel Style Sheet -->
		<link rel="stylesheet" href="panel.style.css" type="text/css">

		<script src="./../assets/js/jquery-1.11.2.min.js"></script>
		<script src="./../assets/js/jquery.selectbox.min.js"></script>

	</head>

	<?php if ( $_SESSION['a-login'] === true ) { ?>
		<body style="padding: 0;">
		<section class="intro small"><div class="container content">
				<a href="?logout" class="pull-right btn btn-danger"><i class="fa fa-sign-out"></i> Logout</a>
				<h2>AIO - Radio Station Player</h2><h3>Control Panel</h3>
			</div>
		</section>
		<?php
		} else { 
		?>
		<body style="background: rgb(245,245,245); background: radial-gradient(ellipse at center, #f0f0f0 0%, #e0e0e0 100%);">
		<?php
		}

	}

	/* Function to display footer of admin pages
	================================================================================== */	
	function footer ($script = '') {
		echo '</section><br><br>';
		global $item;
	?>
	<script type="text/javascript">

		// Cookie(s) function
		function setcookie(e,o,n,t,i,c){var u=new Date;u.setTime(u.getTime()),n&&(n=1e3*n*60*60*24);var r=new Date(u.getTime()+n);document.cookie=e+"="+escape(o)+(n?";expires="+r.toGMTString():"")+(t?";path="+t:"")+(i?";domain="+i:"")+(c?";secure":"")}
		function getcookie(e){var o=document.cookie.indexOf(e+"="),n=o+e.length+1;if(!o&&e!=document.cookie.substring(0,e.length))return null;if(-1==o)return null;var t=document.cookie.indexOf(";",n);return-1==t&&(t=document.cookie.length),unescape(document.cookie.substring(n,t))}
		function delcookie(e,o,n){getcookie(e)&&(document.cookie=e+"="+(o?";path="+o:"")+(n?";domain="+n:"")+";expires=Thu, 01-Jan-1970 00:00:01 GMT")}
		var version = '<?php echo file_get_contents('version.txt'); ?>';

		// On document ready
		$(document).ready(function() {

			// Reset fields
			$('input[allowreset="true"], textarea[allowreset="true"]').each(function() {

				var elm = $(this);

				$(elm).wrap('<div class="input-append"></div>');

				var a = $('<div class="append resetico" title="Click to reset field to default value"><a href="#"><i class="fa fa-refresh"></i></a></div>');

				a.on('click', function() {
					$(elm).val($(elm).attr('placeholder'));
					return false;
				});

				$(elm).after(a);

			});


			// Update check
			var checkupdate = getcookie('aio_radio.update');
			if ( checkupdate == null || checkupdate == 'undefined' ) {

				$.getJSON('https://prahec.com/envato/update?action=check&itemid=<?php echo $item; ?>&method=jsonp&callback=?', function(data) {

					if ( version != null || data.version != null || parseFloat(data.version) <= parseFloat(version) ) {

						setcookie('aio_radio.update', data.version, 3600*6);

						// Check if server version is newer
						if ( parseFloat(data.version) > parseFloat(version) ) {
							$('#tab-updates').append(' <span class="label label-info">v' + parseFloat(data.version) + '</span>');
						}

					}

				});

			} else if ( parseFloat(checkupdate) > parseFloat(version) ) { // Show update message if update is available

				$('#tab-updates').append(' <span class="label label-info">v' + parseFloat(checkupdate) + '</span>');
			}


		});

	</script>
	<?php echo '</body><script type="text/javascript">if (typeof (window.loadinit) == "function") { window.loadinit(); } $("select").selectbox();</script></html>';
	}

	/* Function to display tabs of admin pages
	================================================================================== */
	function tabs () {

		## Array of available Tab linsks
		$tabs = array (
			'<i class="fa fa-home"></i> Home' 				=> 'home',
			'<i class="fa fa-database"></i> Channels' 		=> 'channels',
			'<i class="fa fa-chain"></i> Advanced'			=> 'advanced',
			'<i class="fa fa-cogs"></i> Settings' 			=> 'settings',
			'<i class="fa fa-language"></i> Language(s)'	=> 'lang',
			'<i class="fa fa-cloud-download"></i> Update'	=> 'updates'
		);

		
		## Show version in nav
		$out = '<div class="container"><span class="pull-right" style="margin-top: 25px; margin-right: 15px; color: #fff">Version: <b>
		' . ((is_file('version.txt')) ? file_get_contents('version.txt') : '') . '</b></span><ul class="tabs">';

		
		## Loop
		foreach ($tabs as $tab => $link) {

			if ( $_GET['s'] == $link ) $link .= '" class="active"'; ## Active state
			$out .= "<li><a id=\"tab-{$link}\" href=\"?s={$link}\">{$tab}</a></li>";

		}

		
		echo $out .= '</ul></div><section class="container main">';
		checkserver();

	}


	/* Function to check server capatibility and warn user if not ok! (once only)
	==================================================================================== */
	function checkserver () {

		global $settings;

		// PHP Version
		if ( phpversion() <= 5.2 ) {
			echo alert ('You are running PHP Version <b>' . phpversion() . '</b> while this player and its control panel require version <b>5.3</b> and above!');
		}

		if ( !function_exists('simplexml_load_string') ) {
			echo alert ('PHP is not compiled with SimpleXML extension! This may cause some serious issues!');
		}

		if ( !function_exists('curl_version') ) {
			echo alert ('PHP <b>CURL extension</b> is not enabled! This player and its control panel will not work properly, please fix this issue!');
		}

		if ( !is_writable('./../tmp/cache') ) {
			echo alert ('Directory <b>/tmp/cache/</b> is not writable! This will cause extreme slow performance!
			<br>You can fix this issue by setting <b>chmod</b> of folder <b>/tmp/cache/</b> to <b>755</b>.');
		}

		if ( !is_writable('./../tmp/images') ) {
			echo alert ('Directory <b>/tmp/images/</b> is not writable! You will not be able to upload custom artist images or channel logo(s)!
			<br>You can fix this issue by setting <b>chmod</b> of folder <b>/tmp/images/</b> to <b>755</b>.');
		}

		if ( !is_writable('./../tmp/logs') ) {
			echo alert ('Directory <b>/tmp/logs/</b> is not writable! This means that player will not be able to write error log!
			<br>You can fix this issue by setting <b>chmod</b> of folder <b>/tmp/logs/</b> to <b>755</b>.');
		}


		// Add check for error logs
		if ( is_readable( './../tmp/logs/player.api.log' ) && is_file ( './../tmp/logs/player.api.log' ) && $settings['debugging'] !== 'disabled' ) {

			if ( isset( $_GET['delete'] ) && $_GET['delete'] == 'logfile' ) {

				if ( !unlink( './../tmp/logs/player.api.log' ) ) {

					echo alert ( 'Unable to delete log file, please delete file manually in /tmp/logs/ folder.', error );

				} 

				return true;

			}


			echo alert ( '<h5>Error Log Present</h5>Player might be experiancing some issues because it would seem log file exists. 
			You can <a target="_blank" href="./../tmp/logs/player.api.log"><i class="fa fa-external-link"></i> view</a> or 
			<a onclick="return confirm(\'Are you sure you wish to delete the file?\');" href="?s=' . $_GET['s'] . '&delete=logfile"><i class="fa fa-times"></i> delete</a> the file.', warning ); 

		}

	}

?>