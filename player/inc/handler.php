<?php

	// Include other needed files
	include 'inc/lib/cache.class.php';
	include 'inc/lib/image-resize.class.php';
	if ( is_file('inc/conf/channels.php') ) include 'inc/conf/channels.php';
	if ( !is_array($channels) ) $channels = array();


	// Start few functions and init objects
	header ("Content-Type: application/json");
	$cache = new cache(array('path' => 'tmp/cache/'));


	/* Initial player run, get all channels in nice json object
	============================================================================================== */
	if ( isset( $_GET['c'] ) && $_GET['c'] == 'all' ) {

		if ( is_array($channels[0]) ) {

			// Sort array by name ascending
			foreach ($channels as $key => $row) { $ssby[$key] = $row['name']; } 		## Find common key
			array_multisort($ssby, SORT_ASC, $channels); 								## Sort

			// Output
			$out = array();		## Temp output array
			foreach ($channels as $key => $chn) {

				// Remove sensitive stuff
				unset( $chn['stats'] );
				$out[$key] = $chn;

			}

			$jsondata = json_encode($out);
			echo (( !empty($_GET['callback']) && $settings['api'] == 'true' ) ? "{$_GET['callback']}({$jsondata});" : $jsondata);
			exit;

		} else { // No channels defined

			exitJSON();

		}

	}


	/* URL Parameter C is checked here, if channel doesn't exist, return empty json
	============================================================================================== */
	foreach ( $channels as $key => $channel ) {	if ( $channel['name'] == $_GET['c'] ) break; }	## Find specified channel
	if ( !is_array( $channels[$key] ) ) { echo json_encode( array() ); exit; }					## Specified channel doesn't exist

	// Set few vars before attempting fate :)
	$info 	= array();
	$c 		= $channels[$key];
	$ctime	= ( ( $settings['stats_refresh'] - 1 ) <= 1) ? 10 : ( $settings['stats_refresh']-1 );


	/* Now do the heavy work, use configured method to get stats information
	============================================================================================== */
	switch ($c['stats']['method']) {


		/* Connects to specified stream and opens it as a player, then it reads sent track ID. (NO CURL)
		============================================================================================== */
		case 'direct':

			if ( !$info = $cache->get('stream.' . $key . '.info') ) {

				// Attempt to read few bytes of stream to get current playing track information
				$getinfo = streaminfo( $c['stats']['url'] );

				// Use backup if first failed
				if ( ( empty( $getinfo ) OR $getinfo === false ) AND !empty( $c['fallback'] ) ) {
					$getinfo = streaminfo( $c['fallback'] );
				}


				// Connection failed, log it
				if ( $getinfo === false ) {
					writeLog('player.api', "{$c['name']}: Connection to remote stream {$c['stats']['url']} failed!");
				}

				// Now, result must not be empty or err occured
				
				///Моя функция замены имени артиста
				$filename = '/var/log/icecast2/playlist.log';
				if(file_exists($filename) && is_readable($filename)){
					$array = file($filename);
					$last_elem = count($array)-1;
					list($dates, $mount, $listeners, $last_track) = preg_split("/[|]/", $array[ $last_elem ]);
					preg_match("/^(.+?)\s-\s(.+?)$/", $last_track, $artist_track);
					$current_artist = $artist_track['1'];
					$current_title = $artist_track['2'];
					$title_my = urlencode(stripcslashes($current_title));
					$artist_my = urlencode(stripcslashes($current_artist));
				} else {
					$artist_my = $settings['artist_default'];		
					$title_my = $settings['title_default'];		
				}
		///////////
				
				
				preg_match('/' . $settings['track_regex'] . '/', $getinfo, $track);

				$info['artist'] 	= (( empty($track['artist']) ) ? $artist_my : trim($track['artist']));
				$info['title'] 		= (( empty($track['title']) ) ? $title_my : trim($track['title']));
				$info['image'] 		= getArtwork($track['artist'], $settings);
				$info['status']		= 'no-cache';

				// Cache result
				$cache->set('stream.' . $key . '.info', $info, $ctime);

			} else {

				// Info only
				$info['status']		= 'cached';

			}

			break;



			/* Connect's to shoutcast admin panel and read's XML of a station
			============================================================================================== */
		case 'shoutcast':

			// Check conf first
			if ( empty($c['stats']['url']) OR empty($c['stats']['auth']) ) {

				writeLog('player.api', "{$c['name']}: Invalid configuration! Missing Shoutcast URL or authorization password");
				exitJSON();

			} else if ( !function_exists('curl_version') ) {

				writeLog('player.api', "{$c['name']}: CURL extension is not loaded!");
				exitJSON();

			} else if ( !function_exists('simplexml_load_string') ) {

				writeLog('player.api', "{$c['name']}: SimpleXML extension is not loaded!");
				exitJSON();

			}


			// Check cache
			if ( !$info = $cache->get('stream.' . $key . '.info') ) {

				if ( !$xmlfile = get("{$c['stats']['url']}/admin.cgi?pass={$c['stats']['auth']}&mode=viewxml&sid={$c['stats']['sid']}") ) {

					writeLog('player.api', "{$c['name']}: Connection to Shoutcast server failed!");

				} else {

					$scdata = xml2array($xmlfile, true);

					// Log error if song title is empty
					if ( empty($scdata['songtitle']) ) {
						writeLog('player.api', "{$c['name']}: Unable to get song title, it would seem server response was \"OK\" but result is unknown.");
					}


					// Now, result must not be empty or err occurred
					preg_match('/' . $settings['track_regex'] . '/', $scdata['songtitle'], $track);

					$info['artist'] 	= (( empty($track['artist']) ) ? $settings['artist_default'] : trim($track['artist']));
					$info['title'] 		= (( empty($track['title']) ) ? $settings['title_default'] : trim($track['title']));
					$info['image'] 		= getArtwork($track['artist'], $settings);
					$info['status']		= 'no-cache';

					// Cache result
					$cache->set('stream.' . $key . '.info', $info, $ctime);

				} 

			} else {

				// Info only
				$info['status']		= 'cached';

			}

			break;



			/* Connects to Shoutcast admin panel and reads XML of a station
			============================================================================================== */
		case 'icecast':

			// Check conf first
			if ( empty($c['stats']['url']) OR empty($c['stats']['auth-user']) OR empty($c['stats']['auth-pass']) ) {

				writeLog('player.api', "{$c['name']}: Invalid configuration! Missing Icecast URL, authorization or mount details!");
				exitJSON();

			} else if ( !function_exists('curl_version') ) {

				writeLog('player.api', "{$c['name']}: CURL extension is not loaded!");
				exitJSON();

			} else if ( !function_exists('simplexml_load_string') ) {

				writeLog('player.api', "{$c['name']}: SimpleXML extension is not loaded!");
				exitJSON();

			}


			// Check cache
			if ( !$info = $cache->get('stream.' . $key . '.info') ) {


				// Icecast requires proper HTTP auth, so we provide it!
				if ( !$xmlfile = get("{$c['stats']['url']}/admin/stats", null, "{$c['stats']['auth-user']}:{$c['stats']['auth-pass']}") ) {	

					writeLog('player.api', "{$c['name']}: Connection to Icecast stats server failed!");

				} else if ( preg_match("/You need to authenticate/s", $xmlfile) ) {

					writeLog('player.api', "{$c['name']}: Unable to authorize, login failed!");

				} else { // Now we should have details, attempt to use them.

					$ice = array();
					$icedata = xml2array($xmlfile, true);

					// Multiple mount points
					if ( is_array($icedata['source'][0]) ) {

						foreach ( $icedata['source'] as $mount ) {

							// Parse mount name				
							$mountName = $mount['@attributes']['mount'];
							unset($mount['@attributes']);

							// Make nice array with mount name as key (for fall-back <3)
							$ice[$mountName] = $mount;

						}

						// Single mount point
					} else {

						// Get mount name
						$mountName = $icedata['source']['@attributes']['mount'];
						unset($icedata['source']['@attributes']);

						// Set mount info
						$ice[$mountName] = $icedata['source'];

					}


					// Check if specified mount or fall-back mount exist
					if ( !is_array($ice[$c['stats']['mount']]) AND !is_array($ice[$c['stats']['fallback']]) ) {

						writeLog('player.api', "{$c['name']}: Specified mount and fall-back mount were not found!");

					} else {


						// Attempt to use main mount, else use backup one
						if ( !empty($ice[$c['stats']['mount']]['title']) OR !empty($ice[$c['stats']['mount']]['artist']) ) {

							$icetrack = ((empty($ice[$c['stats']['mount']]['artist'])) ? $ice[$c['stats']['mount']]['title'] : $ice[$c['stats']['mount']]['artist'] . ' - ' . $ice[$c['stats']['mount']]['title']);

						} else { // Backup mount

							$icetrack = ((empty($ice[$c['stats']['fallback']]['artist'])) ? $ice[$c['stats']['fallback']]['title'] : $ice[$c['stats']['fallback']]['artist'] . ' - ' . $ice[$c['stats']['fallback']]['title']);

						}


						// Now, after so much checks and stuff, do track match
						preg_match('/' . $settings['track_regex'] . '/', $icetrack, $track);

						$info['artist'] 	= (( empty($track['artist']) ) ? $settings['artist_default'] : trim($track['artist']));
						$info['title'] 		= (( empty($track['title']) ) ? $settings['title_default'] : trim($track['title']));
						$info['image'] 		= getArtwork($track['artist'], $settings);
						$info['status']		= 'no-cache';

						// Cache
						$cache->set('stream.' . $key . '.info', $info, $ctime);

					}

				}


			} else {

				// Info only
				$info['status']		= 'cached';

			}

			break;



			/* Uses MySQLi extension to connect to the specified stream. This may be the most reliable option
			============================================================================================== */
		case 'sam':

			// Check conf first
			if ( empty($c['stats']['host']) OR empty($c['stats']['auth-user']) OR empty($c['stats']['auth-pass']) OR empty($c['stats']['db']) ) {

				writeLog('player.api', "{$c['name']}: Invalid configuration! Missing all required information to access SAM Broadcaster's database.");
				exitJSON();

			} else if ( !class_exists('mysqli') ) {

				writeLog('player.api', "{$c['name']}: MySQLi extension is not loaded, unable to connect to database!");
				exitJSON();

			}


			// Check for cache
			if ( !$info = $cache->get('stream.' . $key . '.info') ) {

				$db = new mysqli($c['stats']['host'], $c['stats']['auth-user'], $c['stats']['auth-pass'], $c['stats']['db']);

				if ( $db->connect_errno > 0 ) { // Failed to connect

					writeLog('player.api', "{$c['name']}: Database connection failed, MySQL returned: {$db->connect_error}");
					exitJSON();

				} else { // Connected!

					// Fetch SAM history
					$sam = mysqli_fetch_assoc($db->query("
						SELECT songID, artist, title, date_played, duration
						FROM {$c['stats']['db']}.historylist
						ORDER BY `historylist`.`date_played` DESC LIMIT 0 , 1
						"));

					// Check if query failed?
					if ( $db->error ) { // Failed to connect

						writeLog('player.api', "{$c['name']}: SAM Database query failed with error: {$db->error}");
						exitJSON();

					} else {


						// Sometimes SAM ID3 tags are incorrect
						if ( !empty($sam['artist']) AND empty($sam['title']) ) { 

							preg_match('/' . $settings['track_regex'] . '/', $sam['artist'], $track);

						} else if ( empty($sam['artist']) AND !empty($sam['title']) ) {

							preg_match('/' . $settings['track_regex'] . '/', $sam['title'], $track);

						} else {

							$track = $sam;

						}


						// Now, after so much checks and stuff, do track match
						$info['artist'] 	= (( empty($track['artist']) ) ? $settings['artist_default'] : trim($track['artist']));
						$info['title'] 		= (( empty($track['title']) ) ? $settings['title_default'] : trim($track['title']));
						$info['image'] 		= getArtwork($track['artist'], $settings);
						$info['status']		= 'no-cache';

						// Cache
						$cache->set('stream.' . $key . '.info', $info, $ctime);

					}
				}

			} else {

				// Info only
				$info['status']		= 'cached';

			}

			break;



			/* This will connect to Centova-cast API which is usually located on streaming provider. Requires enabled track info widget
			============================================================================================== */
		case 'centovacast':

			// Check config first
			if ( empty($c['stats']['url']) OR empty($c['stats']['user']) ) {

				writeLog('player.api', "{$c['name']}: Invalid configuration! Missing Centova-cast URL or username!");
				exitJSON();				

			} else if ( !function_exists('curl_version') ) {

				writeLog('player.api', "{$c['name']}: CURL extension is not loaded!");
				exitJSON();

			}


			// Check for cache
			if ( !$info = $cache->get('stream.' . $key . '.info') ) {

				if ( !$centova = get("{$c['stats']['url']}/external/rpc.php?m=streaminfo.get&username={$c['stats']['user']}&rid={$c['stats']['user']}&charset=utf8") ) {

					writeLog('player.api', "{$c['name']}: Connection to Centova-cast RPC API failed!");

				} else {

					$centova = json_decode($centova, true);
					if ( !empty($centova['error']) ) {

						writeLog('player.api', "{$c['name']}: Centova-cast returned error: {$centova['error']}!");

					} else {

						$track = $centova['data'][0]['track'];

						// Now, after so much checks and stuff, do track match
						$info['artist'] 	= (( empty($track['artist']) ) ? $settings['artist_default'] : trim($track['artist']));
						$info['title'] 		= (( empty($track['title']) ) ? $settings['title_default'] : trim($track['title']));
						$info['image'] 		= getArtwork($track['artist'], $settings);
						$info['status']		= 'no-cache';

						// Cache
						$cache->set('stream.' . $key . '.info', $info, $ctime);

					}

				}

			} else {

				// Info only
				$info['status']		= 'cached';

			}

			break;



			/* Radionomy is Shoutcast provider who has their own API. Since company purchased Shoutcast this is cool.
			============================================================================================== */
		case 'radionomy':

			// Check config first
			if ( empty($c['stats']['user-id']) OR empty($c['stats']['api-key']) ) {

				writeLog('player.api', "{$c['name']}: Invalid configuration, missing Radionomy Radio ID or API key!");
				exitJSON();	

			} else if ( !function_exists('curl_version') ) {

				writeLog('player.api', "{$c['name']}: CURL extension is not loaded!");
				exitJSON();

			} else if ( !function_exists('simplexml_load_string') ) {

				writeLog('player.api', "{$c['name']}: SimpleXML extension is not loaded!");
				exitJSON();

			}


			// Check for cache
			if ( !$info = $cache->get('stream.' . $key . '.info') ) {

				// Connect to API
				if ( !$onmy = get("http://api.radionomy.com/currentsong.cfm?radiouid={$c['stats']['user-id']}&apikey={$c['stats']['api-key']}&callmeback=yes&type=xml&cover=yes&previous=no") ) {	

					writeLog('player.api', "{$c['name']}: Connection to Radionomy API failed!");

				} else {

					$radioxml = xml2array($onmy, true);

					if ( $radioxml === false || !is_array($radioxml) ) {

						writeLog('player.api', "{$c['name']}: Unable to decode Radionomy response!");

					} else {


						$track = $radioxml['track'];

						// Now, after so much checks and stuff, do track match
						$info['artist'] 	= (( empty($track['artists']) ) ? $settings['artist_default'] : trim($track['artists']));
						$info['title'] 		= (( empty($track['title']) ) ? $settings['title_default'] : trim($track['title']));
						$info['image'] 		= getArtwork( 
							$track['artists'], 
							$settings + array( 'radionomy' => $c['stats']['use-cover'], 'radionomy-cover' => $track['cover'] )
						); ## Special handler for artwork
						$info['status']		= 'no-cache';

						// Cache
						$cache->set('stream.' . $key . '.info', $info, floor($radioxml['track']['callmeback'] / 1000));


					}					

				}


			} else {

				// Info only
				$info['status']		= 'cached';

			}

			break;


			/* Custom (URL) is option that parses ONLY artist - title from external text, php or any other file
			============================================================================================== */
		case 'custom':

			// Check config first
			if ( empty($c['stats']['url']) ) {

				writeLog('player.api', "{$c['name']}: Invalid configuration! Missing Custom URL!");
				exitJSON();	

			} else if ( !function_exists('curl_version') ) {

				writeLog('player.api', "{$c['name']}: CURL extension is not loaded!");
				exitJSON();

			}


			// Check for cache
			if ( !$info = $cache->get('stream.' . $key . '.info') ) {

				// Connect to API
				if ( !$txt = get($c['stats']['url'], false, ((!empty($c['stats']['user']) && !empty($c['stats']['pass'])) ? "{$c['stats']['user']}:{$c['stats']['pass']}" : '')) ) {	

					writeLog('player.api', "{$c['name']}: Connection to Custom URL \"{$c['stats']['url']}\" failed!");

				} else {

					if ( $txt === false || empty($txt) ) {

						writeLog('player.api', "{$c['name']}: Connection to Custom URL was successful but there was no data returned!");

					} else {

						// Now, after so much checks and stuff, do track match
						preg_match('/' . $settings['track_regex'] . '/', $txt, $track);

						$info['artist'] 	= (( empty($track['artist']) ) ? $settings['artist_default'] : trim($track['artist']));
						$info['title'] 		= (( empty($track['title']) ) ? $settings['title_default'] : trim($track['title']));
						$info['image'] 		= getArtwork($track['artist'], $settings);
						$info['status']		= 'no-cache';

						// Cache
						$cache->set('stream.' . $key . '.info', $info, $ctime);

					}					

				}


			} else {

				// Info only
				$info['status']		= 'cached';

			}

			break;



			/* Disabled, simply return defaults
			============================================================================================== */
		case 'disabled':

			// Disabled or ERROR occurred
			$jsondata = json_encode( array(
				'artist'			=>	$settings['artist_default'],
				'title'				=>	$settings['title_default'],
				'image'				=>	getArtwork(null),
				'status'			=>	'disabled'
			) );

			echo (( !empty($_GET['callback']) && $settings['api'] == 'true' ) ? "{$_GET['callback']}({$jsondata});" : $jsondata);
			exit();
			break;


			/* This should not happen, at all.
			============================================================================================== */
		default:
			writeLog('player.api', "{$c['name']}: Invalid method! This is truly fancy error which should never happen!");
			die('AIO - Radio Station Player API');
			break;

	}


	/* Heavy work done, handle data returned from API's and show it in JSON encoded format
	============================================================================================== */
	if ( $info !== false ) {

		// Encode gathered information
		$jsondata = json_encode( $info );

	} else { 

		// Create simple & empty JSON array
		$jsondata = json_encode( array() );

	}


	// Show output (if this is JSONP request, adapt response to its requirements
	echo (( !empty($_GET['callback']) && $settings['api'] == 'true' ) ? "{$_GET['callback']}({$jsondata});" : $jsondata);		


	// If cache is initiated, close & save its status.
	if ( is_object($cache) ) { $cache->quit(); }

?>