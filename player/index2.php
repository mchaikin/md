<?php

	// Log errors into file
	error_reporting(E_ALL ^ E_NOTICE);
	ini_set("log_errors", "on");
	ini_set("error_log", getcwd() . "/tmp/logs/php.log");
	// REMOVE if you do not wish to get logs


	## Include files & settings
	include 'inc/functions.php';
	if ( is_file('inc/conf/general.php') ) include 'inc/conf/general.php';


	## Debugging - Show / Hide PHP errors
	ini_set('display_errors', ( ( $settings['debugging'] == 'enabled' ) ? true : false ) );


	## Language handler
	if ( $settings['multi_lang'] != 'true' ) { 

		include 'inc/lang/' . $settings['default_lang'];

	} else { ## Enabled

		// Multi-language, check browser preference for selected language
		$lang = strtolower( substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 ) );
		if ( file_exists( "inc/lang/{$lang}.php" ) ) { // Load if language is found

			include "inc/lang/{$lang}.php";

		} else { // Fall back to default

			include 'inc/lang/' . $settings['default_lang'];

		}

	}


	## Handle playlists etc...
	if ( isset($_GET['c']) && isset($_GET['pl']) ) {
		include 'inc/playlist-handler.php'; exit;
	}


	## Handle requests & other backend stuff
	if ( isset($_GET['c']) ) {
		include 'inc/handler.php'; exit;
	}


	## Some settings need verification before used, do that here
	$settings['iconsize'] 	= (!is_numeric($settings['playlist_icon_size'])) ? 32 : $settings['playlist_icon_size'];
	$player_vars 			= array_merge($lang, $settings);

	
	## Handle array which will be passed to javascript for language and settings
	$v = array(
		'lang'				=>	$lang,
		'channel'			=>	array(),
		'title'				=>	$settings['title'],
		'default_channel'	=>	$settings['default_channel'],
		'default_artist'	=>	$settings['artist_default'],
		'default_title'		=>	$settings['title_default'],
		'artist_length'		=>	$settings['artist_maxlength'],
		'title_length'		=>	$settings['title_maxlength'],
		'dynamic_title'		=>	$settings['dynamic_title'],
		'usecookies'		=>	$settings['cookie_support'],
		'stats_refresh'		=>	( is_numeric( $settings['stats_refresh'] ) && $settings['stats_refresh'] > 5 ) ? $settings['stats_refresh'] : 15,
		'autoplay'			=>	( ( isset( $_GET['autoplay'] ) && $_GET['autoplay'] == 'false') ? false : $settings['autoplay'] )
	);

	
	// Start output handler and finish with PHP part here.
	ob_start( function($buffer) use ($player_vars) { return bracket2text($buffer, $player_vars); }, 4096);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>{$TITLE}</title>
		<meta name="description" content="{$DESCRIPTION}">
		<meta name="robots" content="index, follow">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<!-- Styles -->
		<link href="assets/css/MD_Theme.css" rel="stylesheet" type="text/css">
		<link href="//fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet" type="text/css">

		<!-- JS -->
		<script type="text/javascript" src="assets/js/jquery-1.11.2.min.js"></script>

	</head>
	<body>

		<!-- Show full screen prelader -->
		<div class="preloader">
			<div class="text_area">
				<noscript style="color: red;"><div style="font-weight: 500;">ERROR OCCURED</div>This player does not work without javascript! <style>#no-js-hide { display: none; }</style></noscript>
				<span id="no-js-hide" style="vertical-align: top;">{$LOADING-MESSAGE}<br><img src="assets/img/preloader.svg" width="48" height="48" alt="preloader"></span>
			</div>
		</div>

		<!-- Playback container, play/stop -->
	<table class="player_main">
		<tr>
		<td width="92px" height="50px">
		<div class="player">
			<div class="playback">

				<div class="play" title="{$UI-PLAY}">
					<svg width="50" height="50" version="1.0" id="button" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
						viewBox="0 10 80 50" enable-background="new 0 0 50 50" xml:space="preserve">
						<!--<circle cx="34" cy="34" r="32"/>-->
						<path fill="#FFFFFF" d="M47.9,32.9L31.4,20c-0.9-0.9-2.5-0.9-3.4,0l0,0c-0.4,0.4-0.9,0.9-0.9,1.3v25.3c0,0.4,0.4,0.9,0.9,1.3l0,0
							c0.9,0.9,2.5,0.9,3.4,0L47.9,35C48.7,34.6,48.7,33.8,47.9,32.9L47.9,32.9z"/>
					</svg>
				</div>

				<div class="stop" style="display: none;" title="{$UI-STOP}">
					<svg width="50" height="50" version="1.1" id="button" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
						viewBox="0 10 80 50" enable-background="new 0 0 50 50" xml:space="preserve">
						<!--<circle cx="34" cy="34" r="32"/>-->
						<path fill="#FFFFFF" d="M42.7,44.7H25.3c-1.1,0-1.9-0.9-1.9-1.9V25.3c0-1.1,0.9-1.9,1.9-1.9h17.5c1.1,0,1.9,0.9,1.9,1.9v17.5
							C44.7,43.8,43.8,44.7,42.7,44.7z"/>
					</svg>
				</div>

			</div>
		</div>
		</td>
		<!-- Artist image, Current stats -->
		<td>
		<div class="stats">
			<div class="onair">
				<div class="now_playing">Now playing:&nbsp;</div>
				<div class="artist">{$ARTIST_DEFAULT}</div>
				<div class="slash"><span>&nbsp;&ndash;&nbsp;</span></div>
				<div class="title">{$TITLE_DEFAULT}</div>
			</div>
		</div>
		</td>
		<td width="92px" height="50px" style="text-align: center;">
			
		<!-- Player, Volume control and Playlist files -->
		

			<!-- jPlayer object, flash and html5 audio container -->
			<div id="jplayer-object"></div>

			<!-- Show playlist files, those are generated automatically via PHP handler, but you can also modify them -->
		<!--<td width="250px">
		<div class="playlists">
				<span>{$UI-PLAYLISTS}</span><br>
				<a tabindex="1" target="_blank" href="?pl=winamp&c=" title="Winamp"><img width="{$ICONSIZE}" height="{$ICONSIZE}" src="assets/img/player-winamp-icon.svg"></a>
				<a tabindex="1" target="_blank" href="?pl=wmp&c=" title="Windows Media Player"><img width="{$ICONSIZE}" height="{$ICONSIZE}" src="assets/img/player-wmp-icon.svg"></a>
				<a tabindex="1" target="_blank" href="?pl=quicktime&c=" title="QuickTime"><img width="{$ICONSIZE}" height="{$ICONSIZE}" src="assets/img/player-quicktime-icon.svg"></a>
				<a tabindex="1" target="_blank" href="?pl=vlc&c=" title="VLC Player"><img width="{$ICONSIZE}" height="{$ICONSIZE}" src="assets/img/player-vlc-icon.svg"></a>
			</div>
</td>-->
</tr>
</table>
	</body>

	<!-- Load after body, should not block rendering! -->
	<script type="text/javascript" src="assets/js/jquery.jplayer.min.js"></script>
	<script type="text/javascript" src="assets/js/aio-radio.min.js"></script>
	<script type="text/javascript">var s = <?php echo json_encode($v); ?>;</script>
	<?php if ( !empty($settings['google_analytics']) ) { ?>
		<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', '<?php echo $settings['google_analytics']; ?>', 'auto');
			ga('send', 'pageview');
		</script>
		<?php 
	} ?>

</html>
<?php ob_end_flush(); ?>