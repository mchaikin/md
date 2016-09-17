<?php

	/* Helper function to write log files
	============================================================================================================================ */
	function writeLog ( $file, $text, $path = 'tmp/logs/' ) {
		
		global $settings; ## Not a perfect solution
		
		if ( $settings['debugging'] == 'disabled' ) return false; ## Logging is disabled!
		if ( is_writable($path) ) return file_put_contents($path . $file . ".log", "[" . date("j.n.Y-G:i") . "] {$text}\n", FILE_APPEND);	

	}

	/* Open stream and read its content to parse current playing track
	============================================================================================================================= */
	function streaminfo ($streamURL) {

		$result = false;
		$icy_metaint = false;

		// Stream context headers
		$opts = array(
		
			'http' => array(
				'method' 			=> 'GET',
				'header'			=> 'Icy-MetaData: 1',
				'user_agent' 		=> 'Mozilla/5.0 (Musical Decadence - Radio Station Player)',
				'timeout'			=>	3,
				'ignore_errors' 	=> true
			)
		);

		$default = stream_context_set_default($opts);
		if ( $stream = @fopen($streamURL, 'r') ) {

			if( $stream && ($meta_data = stream_get_meta_data($stream)) && isset($meta_data['wrapper_data']) ) {

				foreach ($meta_data['wrapper_data'] as $header) { // Seek meta response for icy-metaint (first stream report)

					if (strpos(strtolower($header), 'icy-metaint') !== false) {
						$tmp = explode(":", $header);
						$icy_metaint = trim($tmp[1]);
						break;
					}
				}

			}

			if( $icy_metaint != false ) { // Stream returned metadata refresh time, use it to get streamTitle info.
				$buffer = stream_get_contents($stream, 600, $icy_metaint);

				if(strpos($buffer, 'StreamTitle=') !== false) {
					$title = explode('StreamTitle=', $buffer);
					$title = trim($title[1]);
					$result = substr($title, 1, strpos($title, ';') - 2);
				}
			}


			fclose($stream);
		}                

		if (!$stream) return false; else
			return $result;
	}


	/* CURL function to get data from URL
	============================================================================================================================= */
	function get ( $url, $post = '', $auth = '', $progress = false, $timeout = 5 ) {

		$CURL = curl_init();
		curl_setopt($CURL, CURLOPT_URL, $url);
		curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($CURL, CURLOPT_CONNECTTIMEOUT, ( ( $timeout < 6 && $timeout != 0 ) ? 2 : $timeout ) );
		curl_setopt($CURL, CURLOPT_USERAGENT, 'Mozilla/5.0 (AIO - Radio Station Player)');
		curl_setopt($CURL, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($CURL, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		curl_setopt($CURL, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($CURL, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($CURL, CURLOPT_REFERER, 'http' . (($_SERVER['SERVER_PORT'] == 443) ? 's://' : '://') . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?'));

		if ( isset( $post ) && !empty( $post ) ) { // Also POST
			curl_setopt($CURL, CURLOPT_POSTFIELDS, $post);
			curl_setopt($CURL, CURLOPT_POST, 1);
		}

		if ( isset( $auth ) && !empty( $auth ) ) { // Authorize
			curl_setopt($CURL, CURLOPT_USERPWD, $auth);
		}

		if ( $progress !== false && is_callable( $progress ) ) { // Call progressCallback function
			curl_setopt( $CURL, CURLOPT_NOPROGRESS, false );
			curl_setopt( $CURL, CURLOPT_PROGRESSFUNCTION, $progress );
		}

		// Finnaly execute CURL
		$data = curl_exec($CURL);

		// Parse ERROR 
		if ( curl_error( $CURL ) AND is_writable( 'tmp/logs/curl.log') ) { 
			file_put_contents( 'tmp/logs/curl.log', "[" . date("j.n.Y-G:i") . "]: CURL Request at {$url} failed! LOG: " .  curl_error($CURL) . "\n", FILE_APPEND );
		}	

		curl_close($CURL);
		return $data;
	}


	/* Data upload's handler (returns (array) or (string) error)
	============================================================================= */
	function upload ($form_name, $path = 'data/uploads/', $filename = '') {

		// Extension variable
		$extension = ext_get ($_FILES[$form_name]['name']);


		// Filename
		if (empty($filename)) { // If filename is empty, use uploaded file filename

			$filename = $_FILES[$form_name]['name'];

		} else if ( $filename == '.' ) { // If we used dot, generate random filename

			$filename = uniqid() . '.' . $extension;

		} else { // If filename is set, add extension to it

			$filename .= '.' . $extension;

		}


		// Check if path for upload exists, if not create it
		if (!is_dir($path)) mkdir($path, 0755, true);


		// ERR Handler
		$errors = array(
			UPLOAD_ERR_OK            => "",
			UPLOAD_ERR_INI_SIZE      => "Larger than upload_max_filesize.",
			UPLOAD_ERR_FORM_SIZE     => "Your upload is too big !",
			UPLOAD_ERR_PARTIAL    	 => "Upload partialy completed !",
			UPLOAD_ERR_NO_FILE       => "No file specified !",
			UPLOAD_ERR_NO_TMP_DIR    => "Woops, server error. Please contact us! <span style=\"display:none\">UPLOAD_ERR_NO_TMP_DIR</span>",
			UPLOAD_ERR_CANT_WRITE    => "Woops, server error. Please contact us! <span style=\"display:none\">UPLOAD_ERR_CANT_WRITE</span>",
			UPLOAD_ERR_EXTENSION     => "Woops, server error. Please contact us! <span style=\"display:none\">UPLOAD_ERR_EXTENSION</span>",
			UPLOAD_ERR_EMPTY         => "File is empty.",
			UPLOAD_ERR_NOT_MOVED	 => "Error while saving file !"
		);	


		// Handle results & do last touches
		if ( !empty ($_FILES[$form_name]['error']) ) {

			return $errors[$_FILES[$form_name]['error']];

		} else {

			// Try to move uploaded file from TEMP directory to our new set directory
			if (!move_uploaded_file($_FILES[$form_name]['tmp_name'], $path . $filename)) {
				return $errors["UPLOAD_ERR_NOT_MOVED"];
			}


			// Handle return array
			return array (
				filename		=> $filename,
				path			=> $path . $filename,
				extension		=> $extension,
				mimetype		=> $_FILES[$form_name]['type'],
				size			=> $_FILES[$form_name]['size']		
			);

		}


	}


	/* Get artist image & cache it (Uses Last.fm)
	============================================================================================================================ */
	function getArtwork($artist, $settings = array()) {
		
		// Few varaibles =)
		$extensions = array('jpeg', 'png', 'gif', 'jpg');

		// If artist is empty just return default don't hassle parsing it
		if ( empty( $artist ) OR empty( $settings['lastfm_key'] ) ) {
			foreach ($extensions as $ext) {	if ( is_file("tmp/images/default.{$ext}") ) return "tmp/images/default.{$ext}"; }
			return false;
		}

		// Encode artist name to nice readable name
		$artistimg = artistname($artist);

		// Pre-defined artist images
		foreach ($extensions as $ext) {

			if ( is_file("tmp/images/{$artistimg}.{$ext}") ) {
				return "tmp/images/{$artistimg}.{$ext}";
			}

		}

		// Cached version available
		foreach ( $extensions as $ext ) {

			if ( is_file("tmp/cache/{$artistimg}.{$ext}") ) {
				return "tmp/cache/{$artistimg}.{$ext}";
			}

		}


		// No cached version available, prioritize Radionomy cover
		if ( isset( $settings['radionomy'] ) && $settings['radionomy'] == 'true' && filter_var( $settings['radionomy-cover'], FILTER_VALIDATE_URL ) ) {

			$artistimageURL = $settings['radionomy-cover'];

		} else { // Okay, Radionomy cover empty or option disabled...

			$data = xml2array( get("http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&artist=" . urlencode( trim($artist) ) . "&api_key={$settings['lastfm_key']}", false, false, false, 6) );
			$artistimageURL = ( isset($data['artist']['image'][4]) && !empty( $data['artist']['image'][4] ) ) ? $data['artist']['image'][4] : $data['artist']['image'][3];

			// If data is empty or there is error return default image
			if( empty( $data ) OR !empty( $data['error'] ) ) {
				foreach ( $extensions as $ext ) { if ( is_file( "tmp/images/default.{$ext}" ) ) return "tmp/images/default.{$ext}"; }
				return false;
			}

		}


		// Check if the image URL is valid && Caching is enabled
		if ( filter_var( $artistimageURL, FILTER_VALIDATE_URL ) AND $settings['cache_artist_images'] == 'true' ) { ## Only attempt to get image if url is valid!

			// Caching is enabled, URL is valid, download image to RAM
			$img = get( $artistimageURL, false, false, false, 0 );

		} else if ( filter_var( $artistimageURL, FILTER_VALIDATE_URL ) AND $settings['cache_artist_images'] != 'true' ) {

			// Caching disabled & Image is valid URL, return!
			return $artistimageURL;

		} else {

			// No valid image, return default or simply false
			foreach ( $extensions as $ext ) {	if ( is_file("tmp/images/default.{$ext}") ) return "tmp/images/default.{$ext}"; }
			return false;

		}

		$path = "tmp/cache/{$artistimg}." . ext_get( $artistimageURL );
		file_put_contents( $path, $img );

		// Now resize image to 280x280px via crop class
		$img = new Image( $path );
		$img->resize( '280x280', 'crop' );
		$img->save( $path );

		// Return path to compressed and cached image
		return $path;
	}

	/* Simple function to parse XML files into arrays
	============================================================================================================================ */
	function xml2array($data, $lower = false) {

		$vals = json_decode(json_encode((array)simplexml_load_string($data)),true);

		// Lower / Uppercase array keys
		if ( $lower === true AND is_array($vals) )
			return array_change_key_case($vals, CASE_LOWER);
		else 
			return $vals;

	}


	/* Function to convert {$VARIABLE} brackets with PHP variable
	============================================================================== */
	function bracket2text($text, $array) {

		// Change array to upper characters
		$array 		= array_change_key_case($array, CASE_UPPER);
		$replace 	= preg_match_all("/{\\$.*?}/", $text, $all);

		// Parse text
		for ($i = 0; $i < $replace; $i++) {

			$value 	= str_replace(array('{$', '}'), null, $all['0'][$i]); // Match all {} and remove them so we can return value from array
			$text 	= str_replace('{$' . $value . '}', $array[$value], $text);

		}

		return $text;

	}


	/* Shorten strings via specified length
	============================================================================== */
	function shorten ($text, $length) {
		$text = strip_tags($text);

		$length = abs((int)$length);
		if(strlen($text) > $length) {
			$text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
		}
		return($text);
	}


	/* Short function to parse any url format e.g.: http://name.com:port/folder/playlist.pls to http://host:port
	============================================================================= */
	function parseURL ( $url ) {

		// Empty
		if ( empty($url) ) { return null; }	

		// Regex
		preg_match('#^(?P<proto>.*:)//(?P<host>[a-z0-9\-.]+):?(?P<port>[0-9]+)?/?(.*)$#i', $url, $match);

		// Make sure URL is ok before returning...
		if ( empty($match['host']) ) {

			return null;

		} else if ( !is_numeric($match['port']) ) { // No port or not numberic, default to 80

			$match['port'] = 80;

		}


		// Host isn't empty, return :)		
		return "http://{$match['host']}:{$match['port']}";

	}


	/* Short function to speed up deployment of alerts
	============================================================================== */
	function alert ($text, $mode = 'warning', $close = true) {

		if ( $mode == 'warning' ) {

			$mode = 'alert-icon alert-warning';
			$text = '<i class="fa fa-warning"></i><div class="content">' . $text . '</div>';

		} else if ( $mode == 'error' ) {

			$mode = 'alert-icon alert-error';
			$text = '<i class="fa fa-times-circle"></i><div class="content">' . $text . '</div>';

		} else if ( $mode == 'success' ) {

			$mode = 'alert-icon alert-success';
			$text = '<i class="fa fa-check"></i><div class="content">' . $text . '</div>';

		} else if ( $mode == 'info' ) {

			$mode = 'alert-icon alert-info';
			$text = '<i class="fa fa-info-circle"></i><div class="content">' . $text . '</div>';

		}

		return '<div class="alert ' . $mode . '">' . $text . '</div>';
	}


	/* File functions (ext_get, ext_del, etc...)
	============================================================================== */
	function ext_get ($filename) {
		return strtolower(str_replace('.', '', strrchr($filename, '.')));
	}

	function ext_del ($filename) {
		$ext = strrchr($filename, '.');
		return ((!empty($ext)) ? substr($filename, 0, -strlen($ext)) : $filename);
	}

	function file_size ($b,$p = null) {

		$units = array("B","KB","MB","GB","TB","PB","EB","ZB","YB");
		$c=0;
		if(!$p && $p !== 0) {
			foreach($units as $k => $u) {
				if(($b / pow(1024,$k)) >= 1) {
					$r["bytes"] = $b / pow(1024,$k);
					$r["units"] = $u;
					$c++;
				}
			}
			return number_format($r["bytes"],2) . ' ' . $r["units"];
		} else {
			return number_format($b / pow(1024,$p)) . ' ' . $units[$p];
		}
	}

	function getlist ($path) {

		$files = array();
		if ($handle = opendir($path)) {

			while (false !== ($entry = readdir($handle))) {

				// Skip back folder signs
				if ($entry == "." && $entry == "..") continue;

				if (is_dir($path . $entry)) { $entry .= '/'; } // Append / to directories

				// If specified dirs will be skipped
				if (is_dir($path . $entry)) continue;

				$files[] = $entry;

			}

			closedir($handle);

		}

		return $files;

	}


	/* Small function to handle artist image names
	============================================================================== */
	function artistname ( $string ) {

		// Replace some known strings with text
		$string = str_replace(
			array('&', 'ft.'),
			array('and', 'feat'),
			$string
		);

		// Rep
		$arr = array(
			'/[^a-z0-9\.]+/i'	=>	'.',	// Replace all non-standard strings with dot
			'/[\.]{1,}/'		=>	'.'		// Replace multiple dots in same string
		);

		// Replace bad characters
		$string = preg_replace(array_keys($arr), $arr, trim($string));
		return rtrim(strtolower($string), '.');

	}


	/* Very small function to exit JSON with grace
	============================================================================== */
	function exitJSON() {

		if (ob_get_level()) ob_end_clean(); // Clean buffer

		echo json_encode(array());
		exit;
	}

?>