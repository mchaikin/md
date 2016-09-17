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
		<link href="assets/css/radio.css" rel="stylesheet" type="text/css">
		<link href="//fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet" type="text/css">

		<!-- Fav and touch icons -->
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="icon" type="image/png" sizes="192x192" href="assets/img/favicon-192x192.png">
		<link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-96x96.png">
		<link rel="icon" type="image/png" sizes="96x96" href="assets/img/favicon-16x16.png">
		<link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon-32x32.png">

		<!-- Facebook -->
		<?php if( preg_match('/^Facebook/i', $_SERVER['HTTP_USER_AGENT']) ) {

			$CCURL 	= explode("?", $_SERVER['REQUEST_URI']);
			$URL 	= "http://". $_SERVER['HTTP_HOST'] . $CCURL[0];

			echo '<meta property="og:title" content="' . $settings['title'] . '" />
			<meta property="og:url"    content="' . $URL . '" />
			<meta property="og:type"   content="music.radio_station" /> 
			<meta property="og:image" content="' . ( ( empty( $settings['fb_shareimg'] ) ) ? $URL . getArtwork(null) : $settings['fb_shareimg']) . '" />
			<meta property="og:description" content="' . $settings['description'] . '">
			';

		} ?>

		<!-- JS -->
		<script type="text/javascript" src="assets/js/jquery-1.11.2.min.js"></script>

	</head>
	<body>

		<!-- Show full screen prelader -->
		<div class="preloader">
			<div class="text_area">
				<noscript style="color: red;"><div style="font-weight: 500;">ERROR OCCURED</div>This player does not work without javascript! <style>#no-js-hide { display: none; }</style></noscript>
				<span id="no-js-hide">{$LOADING-MESSAGE}<br><img src="../img/loading.gif" alt="preloader"></span>
			</div>
		</div>

		<!-- Artist image, Current stats -->
		<div class="stats">

			<div class="artist-image">
				<div class="share-area">
					<span>{$SHARE}</span>
					<a class="facebook" href="#"><img width="36" height="36" src="assets/img/icon-facebook.svg"></a>
					<a class="vk" href="#"><img width="36" height="36" src="assets/img/icon-vk.png"></a>
					<a class="twitter" href="#"><img width="36" height="36" src="assets/img/icon-twitter.svg"></a>
				</div>
				<div class="artist-img">
					<img width="140" height="140" id="artist-img" src="<?php echo getArtwork(null); ?>">
				</div>
				<div class="artist-preload" style="display: none;"></div>
			</div>

			<div class="onair">
				<div class="artist">{$ARTIST_DEFAULT}</div>
				<div class="title">{$TITLE_DEFAULT}</div>
				<div class="time">00:00</div>
			</div>

		</div>

		<!-- Player, Volume control and Playlist files -->
		<div class="player">

			<!-- jPlayer object, flash and html5 audio container -->
			<div id="jplayer-object"></div>

			<!-- Playback container, play/stop -->
			<div class="playback">

				<div class="play" title="{$UI-PLAY}">
					<svg width="68" height="68" version="1.0" id="button" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
						viewBox="0 0 68 68" enable-background="new 0 0 68 68" xml:space="preserve">
						<circle cx="34" cy="34" r="32"/>
						<path fill="#FFFFFF" d="M47.9,32.9L31.4,20c-0.9-0.9-2.5-0.9-3.4,0l0,0c-0.4,0.4-0.9,0.9-0.9,1.3v25.3c0,0.4,0.4,0.9,0.9,1.3l0,0
							c0.9,0.9,2.5,0.9,3.4,0L47.9,35C48.7,34.6,48.7,33.8,47.9,32.9L47.9,32.9z"/>
					</svg>
				</div>

				<div class="stop" style="display: none;" title="{$UI-STOP}">
					<svg width="68" height="68" version="1.1" id="button" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
						viewBox="0 0 68 68" enable-background="new 0 0 68 68" xml:space="preserve">
						<circle cx="34" cy="34" r="32"/>
						<path fill="#FFFFFF" d="M42.7,44.7H25.3c-1.1,0-1.9-0.9-1.9-1.9V25.3c0-1.1,0.9-1.9,1.9-1.9h17.5c1.1,0,1.9,0.9,1.9,1.9v17.5
							C44.7,43.8,43.8,44.7,42.7,44.7z"/>
					</svg>
				</div>

			</div>

			<!-- Volume control container, also for player status etc.. -->
			<div class="volume-control">

				<div class="volume-icon">
					<svg id="volume" height="28" width="28" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" fill="#45689d"><path d="M6 18v12h8l10 10V8L14 18H6zm27 6c0-3.53-2.04-6.58-5-8.05v16.11c2.96-1.48 5-4.53 5-8.06zM28 6.46v4.13c5.78 1.72 10 7.07 10 13.41s-4.22 11.69-10 13.41v4.13c8.01-1.82 14-8.97 14-17.54S36.01 8.28 28 6.46z"/><path d="M0 0h48v48H0z" fill="none"/></svg>
					<svg id="muted" height="28" width="28" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" fill="#45689d"><path d="M33 24c0-3.53-2.04-6.58-5-8.05v4.42l4.91 4.91c.06-.42.09-.85.09-1.28zm5 0c0 1.88-.41 3.65-1.08 5.28l3.03 3.03C41.25 29.82 42 27 42 24c0-8.56-5.99-15.72-14-17.54v4.13c5.78 1.72 10 7.07 10 13.41zM8.55 6L6 8.55 15.45 18H6v12h8l10 10V26.55l8.51 8.51c-1.34 1.03-2.85 1.86-4.51 2.36v4.13c2.75-.63 5.26-1.89 7.37-3.62L39.45 42 42 39.45l-18-18L8.55 6zM24 8l-4.18 4.18L24 16.36V8z"/><path d="M0 0h48v48H0z" fill="none"/></svg>
				</div>

				<div class="volume-slider">
					<div class="vol-progress"><div class="vol-bar"><div class="circle-control" title="{$UI-VOLUME-CIRCLE}"></div></div></div>
					<div class="player-status"></div>
				</div>

			</div>

			<!-- Show playlist files, those are generated automatically via PHP handler, but you can also modify them -->
			<div class="playlists">
				<span>{$UI-PLAYLISTS}</span>
				<a tabindex="1" target="_blank" href="?pl=winamp&c=" title="Winamp"><img width="{$ICONSIZE}" height="{$ICONSIZE}" src="assets/img/player-winamp-icon.svg"></a>
				<a tabindex="1" target="_blank" href="?pl=wmp&c=" title="Windows Media Player"><img width="{$ICONSIZE}" height="{$ICONSIZE}" src="assets/img/player-wmp-icon.svg"></a>
				<a tabindex="1" target="_blank" href="?pl=quicktime&c=" title="QuickTime"><img width="{$ICONSIZE}" height="{$ICONSIZE}" src="assets/img/player-quicktime-icon.svg"></a>
				<a tabindex="1" target="_blank" href="?pl=vlc&c=" title="VLC Player"><img width="{$ICONSIZE}" height="{$ICONSIZE}" src="assets/img/player-vlc-icon.svg"></a>
			</div>

		</div>

	</body>

	<!-- Load after body, should not block rendering! -->
	<script type="text/javascript" src="assets/js/jquery.jplayer.min.js"></script>
	<script type="text/javascript" src="assets/js/aio-radio.js"></script>
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
		<?php print_r($artist);
	} ?>

</html>
<?php ob_end_flush(); ?>