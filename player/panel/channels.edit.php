<?php if ( $inc !== true ) { header("Location: index.php?s=home"); exit; }

	// Options (might change in future)
	$codecs 		= array ('mp3' => 'MP3', 'oga' => 'OGG');
	$logo_ext 		= array ('jpeg', 'png', 'gif', 'jpg', 'svg');
	$streamurl_ext	= array ('pls', 'm3u', 'xspf');
	$form			= new form;


	// Attempt to delete logo from existing channel
	if ( $_GET['logo'] == 'delete') {
		@unlink('./../' . $channels[$_GET['e']]['logo']);
		exit;
	}


	// Handle POST
	if ( isset($_POST['submit']) ) {

		$_POST['name'] = trim($_POST['name']);

		// Verify fields
		if ( empty($_POST['name']) ) {

			echo alert ('You need to specify name of the channel you are creating or editing.', error);

		} else if ( !is_array($_POST['quality']) OR empty($_POST['url_0'][0]) ) {

			echo alert ('You have to configure streams! Player does not work without them.', error);

			// Success
		} else {


			// Handle upload
			if ( !empty($_FILES['logo']['tmp_name']) ) {

				$filename = "logo." . time();

				// Before continue, delete old image
				if ( $_GET['e'] != 'add' && !empty($channels[$_GET['e']]['logo']) ) {
					@unlink("./../{$channels[$_GET['e']]['logo']}"); // Delete old image
				}

				// Attempt to save
				$up = upload('logo', './../tmp/images/', $filename);
				if ( !is_array($up) ) {

					$error = alert("Uploading logo failed! ERROR: {$up}", error);

				} else if ( !in_array(ext_get($up['path']), $logo_ext) ) {

					$error = alert("Invalid image format! You can only upload JPEG, JPG, PNG, GIF and SVG images!", error);
					@unlink($up['path']);

				} else { // Save success, now do tell!

					$logopath = str_replace('./../tmp/', 'tmp/', $up['path']);

					if ( ext_get($up['path']) != 'svg' ) { // Only resize if not SVG

						// Calculate crop width by having set height
						$imgsize = getimagesize($up['path']);
						$calcwdth = $imgsize[0] / ($imgsize[1] / 80);

						// Crop
						$img = new image ($up['path']);
						$img->resize("{$calcwdth}x80", 'auto');
						$img->save($up['path']);	

					}

				}
			}


			// Convert quality group's POST to a nicer PHP valid array
			$c = count($_POST['quality'])-1;
			$quality_groups = array();

			for ( $i = 0; $i <= $c; $i++ ) { ## LOOP

				$streamname = $_POST['quality'][$i];

				// Count fields
				$name = 'url_' . $i;
				$totalFields = count($_POST[$name])-1;
				$streams = array();

				// LOOP
				for ( $f=0; $f <= $totalFields; $f++ ) {

					$codec = $_POST['codec_' . $i][$f];
					$streams[$codec] = $_POST[$name][$f];

					if ( !filter_var($_POST[$name][$f], FILTER_VALIDATE_URL) ) { // Validate if the stream URL is actually an URL or not

						$error = alert ('Stream URL <b>"' . $_POST[$name][$f] . '"</b> is not valid url!<br>
							Please read section <b>"How to configure streams?"</b> bellow.', error);

					} else if ( in_array( ext_get($_POST[$name][$f]), $streamurl_ext) ) { // Check if stream URL is a playlist

						$error = alert ('Stream URL <b>"' . $_POST[$name][$f] . '"</b> is a playlist file, not an actual stream!<br>
							Please read section <b>"How to configure streams?"</b> bellow.', error);

					}

				}

				// Update groups
				$quality_groups[$streamname] = $streams;

			}


			// Attempt to check stats config and create output conf
			if ( empty($error) ) {

				switch ($_POST['stats']) {

					// Use direct method
					case 'direct':

						if ( !filter_var($_POST['direct-url'], FILTER_VALIDATE_URL) OR ( !empty($_POST['direct-url-fallback']) && !filter_var($_POST['direct-url-fallback'], FILTER_VALIDATE_URL)) ) {
							$error = alert ('Configured stream URL for stats is not valid. Please enter real URL to the stream.', error);
						}

						$stats = array(
							'method'		=>	'direct',
							'url'			=>	$_POST['direct-url'],
							'fallback'		=>	$_POST['direct-url-fallback']
						);
						break;

						// Shoutcast Method
					case 'shoutcast': 

						// Check if Shoutcast admin URL can be parsed
						if ( parseURL($_POST['shoutcast-url']) == null ) {
							$error = alert('Shoutcast Stats URL could not be determinated. Please use <b>http://url-to-server:port</b> format.', error);	
						}

						// Output array
						$stats = array(
							'method' 			=>	'shoutcast',
							'url'				=>	parseURL($_POST['shoutcast-url']),
							'auth'				=>	$_POST['shoutcast-pass'],
							'sid'				=>	$_POST['shoutcast-sid']);
						break;

						// Icecast Method
					case 'icecast': 

						// Check if Icecast admin URL can be parsed
						if ( parseURL($_POST['icecast-url']) == null ) {
							$error = alert('Icecast Stats URL could not be determinated. Please use <b>http://url-to-server:port</b> format.', error);	
						}

						// Output array
						$stats = array(
							'method' 			=>	'icecast',
							'url'				=>	parseURL($_POST['icecast-url']),
							'auth-user'			=>	$_POST['icecast-user'],
							'auth-pass'			=>	$_POST['icecast-pass'],
							'mount'				=>	$_POST['icecast-mount'],
							'fallback'			=>	$_POST['icecast-fallback-mount']);
						break;

						// SAM Broadcaster Method
					case 'sam': $stats = array(
						'method' 			=>	'sam',
						'host'				=>	$_POST['sam-host'],
						'auth-user'			=>	$_POST['sam-user'],
						'auth-pass'			=>	$_POST['sam-pass'],
						'db'				=>	$_POST['sam-db']);
						break;

						// Centovacast Method
					case 'centovacast': 

						// Check if Centovacast panel URL can be parsed
						if ( parseURL($_POST['centova-url']) == null ) {
							$error = alert('Centovacast control panel URL could not be determinated. Please use <b>http://url-to-server:port</b> format.', error);	
						}

						// Output array
						$stats = array(
							'method' 			=>	'centovacast',
							'url'				=>	parseURL($_POST['centova-url']),
							'user'				=>	$_POST['centova-user']);
						break;

						// Radionomy method
					case 'radionomy': $stats = array(
						'method' 			=>	'radionomy',
						'user-id'			=>	$_POST['radionomy-uid'],
						'api-key'			=>	$_POST['radionomy-apikey'],
						'use-cover'			=>	$_POST['radionomy-use-cover']);
						break;

						// Custom URL method
					case 'custom': $stats = array(
						'method' 		=>	'custom',
						'url'			=>	$_POST['custom-url'],
						'user'			=>	$_POST['custom-user'],
						'pass'			=>	$_POST['custom-pass']);
						break;

					case 'disabled':
						$stats = array('method' => 'disabled');
						break;

					default:
						$error = alert('Invalid stats configuration! Can not continue!', error);
						break;	

				}


				// We just used switch done here ;)

			}


			// Prepare output config array
			$conf[] = array(
				'name'			=>	htmlentities($_POST['name']),
				'logo'			=>	(( empty($logopath) ) ? $channels[$_GET['e']]['logo'] : $logopath),
				'skin'			=>	$_POST['skin'],
				'show-time'		=>	(($_POST['show-time'] != 'true') ? false : true),
				'streams'		=>	$quality_groups,
				'stats'			=>	$stats
			);


			// If we already have channels, merge existing data
			if ( $_GET['e'] != 'add' AND empty($error) ) { ## EDIT

				$confout = $channels;
				$confout[$_GET['e']] = $conf[0];

			} else if ( is_array($channels) AND empty($error) ) { ## Merge new channels with existing ones

				$confout = array_merge ($channels, $conf);

			} else {

				$confout = $conf;

			}


			// If any of above action's issued error, show it to user, otherwise save to file
			if ( !empty($error) ) {

				echo $error;

			} else if ( file_put_contents('./../inc/conf/channels.php', '<?php $channels = ' . var_export($confout, true) . '; ?>') ) {

				$_SESSION['msg'] = alert ('Successfully ' . (( $_GET['e'] == 'add') ? 'added' : 'updated') . ' channel.', success);
				header ("Location: ?s=channels");
				exit;

			} else {

				echo alert ('Unable to save configuration into file.<br>Please make sure that file "/inc/conf/channels.php" is writable (chmod 755)!', error);

			}

		}

	}


	// Not submit & not new file
	if ( $_GET['e'] != 'add' && !isset($_POST['submit']) ) { 


		if ( empty($channels[$_GET['e']]) OR !is_numeric($_GET['e']) ) {
			$_SESSION['msg'] = alert ('Unable to edit specified channel because it was not found!');
			header ("Location: ?s=channels");
			exit;
		}


		// Only Convert PHP array of streams to html compatable one if its available
		if ( is_array($channels[$_GET['e']]['streams']) ) { 

			// Few preset variables
			$cid = $_GET['e'];
			$_POST = $channels[$cid];
			$countq = 0;

			// Convert PHP array of streams to html compatable one
			foreach ( $channels[$cid]['streams'] as $name => $arr ) {

				$_POST['quality'][$countq] = $name;

				foreach ( $arr as $codec => $url ) {
					$_POST['url_' . $countq][] = $url;
					$_POST['codec_' . $countq][] = $codec;
				}

				$countq++; ## Increse counter
			}

			unset($_POST['streams']);

		} // End convert


		// Parse config stats
		$stats = $channels[$cid]['stats'];
		switch ($stats['method']) {

			case 'direct':
				$_POST['stats'] 				= $stats['method'];
				$_POST['direct-url'] 			= $stats['url'];
				$_POST['direct-url-fallback'] 	= $stats['fallback'];
				break;

			case 'shoutcast':
				$_POST['stats'] 				= $stats['method'];
				$_POST['shoutcast-url'] 		= $stats['url'];
				$_POST['shoutcast-pass'] 		= $stats['auth'];
				$_POST['shoutcast-sid']			= $stats['sid'];
				break;

			case 'icecast':
				$_POST['stats'] 				= $stats['method'];
				$_POST['icecast-url'] 			= $stats['url'];
				$_POST['icecast-user'] 			= $stats['auth-user'];
				$_POST['icecast-pass'] 			= $stats['auth-pass'];
				$_POST['icecast-mount']			= $stats['mount'];
				$_POST['icecast-fallback-mount']= $stats['fallback'];
				break;

			case 'sam':
				$_POST['stats'] 				= $stats['method'];
				$_POST['sam-host'] 				= $stats['host'];
				$_POST['sam-user'] 				= $stats['auth-user'];
				$_POST['sam-pass'] 				= $stats['auth-pass'];
				$_POST['sam-db']				= $stats['db'];
				break;

			case 'centovacast':
				$_POST['stats'] 				= $stats['method'];
				$_POST['centova-url'] 			= $stats['url'];
				$_POST['centova-user'] 			= $stats['user'];
				break;

			case 'radionomy':
				$_POST['stats'] 				= $stats['method'];
				$_POST['radionomy-uid'] 		= $stats['user-id'];
				$_POST['radionomy-apikey'] 		= $stats['api-key'];
				$_POST['radionomy-use-cover']	= $stats['use-cover'];
				break;

			case 'custom':
				$_POST['stats'] 				= $stats['method'];
				$_POST['custom-url'] 			= $stats['url'];
				$_POST['custom-user'] 			= $stats['user'];
				$_POST['custom-pass'] 			= $stats['pass'];
				break;

			default:
				$_POST['stats'] = 'disabled';
				break;

		}

	}
?>
<div class="divider"></div>
<form method="POST" action="?s=channels&e=<?php echo $_GET['e']; ?>" enctype="multipart/form-data">

	<div class="form-group">
		<label class="control-label col-sm-2" for="name">Channel Name</label>
		<div class="col-sm-5">
			<input class="form-control" type="text" name="name" id="name" value="<?php echo $_POST['name']; ?>" placeholder="Rock channel">
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-2 control-label" for="logo">Channel Logo</label>
		<div class="col-sm-8">
			<div class="file-input">

				<input type="file" id="logo" name="logo">

				<div class="input-group col-sm-6">
					<input type="text" class="form-control file-name" placeholder="Select an image">
					<div class="input-group-btn">
						<a href="#" class="btn btn-info"><i class="fa fa-folder-open fa-fw"></i> Browse</a>
					</div>
				</div>
			</div>
			<i>JPEG, JPG, PNG, GIF and SVG accepted. Image will be cropped to fit logo area.</i>
			<?php if ( !empty($channels[$_GET['e']]['logo']) && is_file('./../' . $channels[$_GET['e']]['logo']) ) { 
				echo '<div class="logo-container"><br><div class="channel-logo">
				<img src="./../' . $channels[$_GET['e']]['logo'] . '" width="auto" height="40"></div><br><a href="#" class="delete-logo"><i class="fa fa-times"></i> Delete</a></div>'; 
			} ?>
		</div>
	</div>

	<div class="clearfix"></div>

	<div class="form-group">

		<label class="col-sm-2 control-label" for="skin">Default theme</label>

		<div class="col-sm-3">
			<select class="form-control" name="skin" id="skin">
				<?php

					// Read skins dir
					$skins = getlist('./../assets/css/');
					foreach ($skins as $skin) {
						
						if ( ext_get( $skin ) != 'css' ) continue; ## Skip files without CSS extension
						
						// Make expected files look nicer
						switch ( $skin ) {
							case 'basic.style.css':	$name = 'Default Light'; break;
							case 'basic.dark.css':	$name = 'Default Light'; break;
							case 'html5-radio.css':	$name = 'HTML5 Player'; break;
							default: $name = ucfirst( ext_del( $skin ) ); break;
						}

						echo '<option value="' . $skin . (($_POST['skin'] == $skin) ? '" selected="selected' : '') . '">' . $name . '</option>';

					}	


				?>
			</select>
		</div>
		<span class="help-block">Hint: Generate custom color schemes under <b>Advanced</b> tab</span>
	</div>

	<div class="form-group">
		<label class="control-label col-sm-2" for="showtime">Channel Time</label>
		<div class="col-sm-8">
			<div class="checkbox">
				<label>
					<input type="checkbox" value="true" name="show-time" id="showtime"<?php if ($_POST['show-time'] == 'true' OR !isset($_POST['show-time']) ) echo ' checked=""'; ?>>
					<span class="fa fa-check"></span> Show playback timer (Based on when the last track was changed)
				</label>
			</div>
		</div>
	</div>

	<div class="divider"></div>

	<div class="row">
		<div class="col-sm-9 col-sm-offset-2">
			<p>
				<b>How to configure streams?</b><br>
				Player supports various streaming formats but because <b>HTML5 Audio API</b> relies on web browser each web browser has different codecs support.
				MP3 codec is supported in all major web browsers, for that reason its highly recommended. Codecs like <b>AAC+</b> and <b>OGG</b> are only supported in small amount of browsers.
				Below you will find examples for how to link streams:
			</p>
			<ul>
				<li><b>Shoutcast v1.x</b> - http://shoutcast-server-url.com:port/;stream.mp3</li>
				<li><b>Shoutcast v2.x</b> - http://shoutcast-server-url.com:port/mountpoint</li>
				<li><b>Iceacast v2.x</b> - http://icecast-server-url.com:port/mountpoint</li>
			</ul>

			<div class="text-red">
			Note: You can use combination of codecs e.g. OGG and MP3. In combination mode first stream is used as "primary" and second as "fall-back". 
			Adding AAC+ codec may break player in some browsers because some browsers don't fall-back when playback fails.
			</div>
		</div>
	</div>

	<div class="clearfix"></div><br>

	<div class="row">
		<label class="col-sm-2 control-label">Configure Streams (Music)</label>
		<div class="col-sm-9">

			<div class="qualitylist">

				<?php

					// If this is post, or edit create quality/streams inputs
					if ( is_array($_POST['quality']) ) { 

						// LOOP
						$c = count($_POST['quality'])-1;
						for ($i = 0; $i <= $c; $i++ ) {

							echo '<div class="quality-group">
							<input title="Click to edit" class="input-quality" type="text" name="quality[]" value="' . $_POST['quality'][$i] . '">
							<div class="pull-right"><a href="#" class="delgrp"><i class="fa fa-times"></i> Delete Group</a></div>
							<table class="table streams"><tbody>';

							// Count fields
							$name = 'url_' . $i;
							$totalFields = count($_POST[$name])-1;

							// Loop through fields
							for ($f=0; $f <= $totalFields; $f++) {

								echo '<tr>
								<td class="col-sm-9"><input class="form-control" type="url" placeholder="Stream URL (read above!)" name="url_' . $i . '[]" value="' . $_POST['url_' . $i][$f] . '"></td>
								<td class="col-sm-2">
								<select name="codec_' . $i . '[]" class="form-control">';

								foreach ( $codecs as $codec => $name ) {
									if ( $_POST['codec_' . $i][$f] == $codec ) { $codec .= '" selected="selected'; } // Select codec
									echo '<option value="' . $codec . '">' . $name . '</option>' . "\n";
								}

								echo '</select>
								</td><td style="width: 5%; text-align: center;"><div class="form-control-static"><a class="remove-row" href="#" style="color: red;"><i class="fa fa-times"></i></a></div></td></tr>';

							} 

							echo '</tbody></table><a href="#" class="addrow"><i class="fa fa-plus"></i> Add More Streams</a></div>';

						}
					}
				?>

			</div>

			<a class="btn btn-success addgrp"><i class="fa fa-plus"></i> Add Another Group</a>

		</div>

	</div>

	<div class="divider"></div>

	<div class="form-group">
		<label class="col-sm-2 control-label" for="stats">Configure Track Info</label>
		<div class="col-sm-4">
			<select class="form-control" name="stats" id="stats">
				<?php

					$vals = array(
						'disabled'		=>	'disabled',
						'direct'		=>	'Use live stream (no login)',
						'shoutcast'		=>	'Shoutcast (login required)',
						'icecast'		=>	'Icecast (login required)',
						'sam'			=>	'SAM Broadcaster (MySQL)',
						'radionomy'		=>	'Radionomy API (UID & API Key)',
						'centovacast' 	=>	'CentovaCast API (no login)',
						'custom'		=>	'Custom (External API)'
					);

					foreach ($vals as $key => $row) {

						if ( $_POST['stats'] == $key ) $key .= '" selected="selected';
						echo '<option value="' . $key . '">' . $row . '</option>';

					}
				?>
			</select>
		</div>
	</div>

	<div class="stats-conf"></div>

	<div class="form-controls">
		<div class="row">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" name="submit" value="save" class="btn btn-success"><i class="fa fa-pencil fa-fw"></i> Save</button>
				<a href="?s=channels" class="btn btn-danger"><i class="fa fa-times fa-fw"></i> Cancel</a>
			</div>
		</div>
	</div>

</form>

<style>
	ul { padding-left: 20px; }
	h5 { font-size: 14px; padding: 0 0 5px; margin: 0; }
	h5 a { font-size: 12px; font-weight: normal; }
	.quality-group { margin:8px 0 30px; }
	.quality-group:last-child { margin-bottom: 20px; }
	table.table { margin: 0 0 5px; }
	table .col-sm-9, table .col-sm-2, tbody td, table.table tr td:first-child { padding: 10px 0 !important; }

	.input-quality {
		position: relative;
		padding: 0;
		margin-bottom: -5px;
		background: transparent;
		border: 0;
		outline-style: none;
		outline: 0;
		font-size: 14px;
		font-weight: 500;
		min-width: 350px;
	}

	.channel-logo {
		display: inline-block;
		border: 1px solid #808080;
		background: #585858;
		color: #fff;
		padding: 5px 10px;
	}
</style>

<script type="text/javascript">

	window.loadinit = function() {

		// Stats inputs
		$('select#stats').on('change', function() {

			<?php

				// Direct stats
				$form->clear();
				$form->add( array( label => 'Stream URL', name => 'direct-url', placeholder => 'http://192.168.1.1:8000/mount', 'class' => 'col-sm-5' ) );
				$form->add( array( label => 'Stream URL (Fallback)', name => 'direct-url-fallback', placeholder => 'http://192.168.1.1:8000/fallback-mount', 'class' => 'col-sm-5', description => '(not required)' ) );
				$direct = $form->html;


				// Shoutcast stats
				$form->clear();
				$form->add( array( label => 'Shoutcast Status Page', name => 'shoutcast-url', placeholder => 'http://192.168.1.1:8000/', 'class' => 'col-sm-5' ) );
				$form->add( array( label => 'Admin Password', name => 'shoutcast-pass', placeholder => 'password', 'class' => 'col-sm-5', type => 'password' ) );
				$form->add( array( label => 'SID', name => 'shoutcast-sid', placeholder => '1', 'class' => 'col-sm-2', description => '(Leave empty if running version 1.x)' ) );
				$shoutcast = $form->html;


				// Icecast stats
				$form->clear();
				$form->add( array( label => 'Icecast Status Page', name => 'icecast-url', placeholder => 'http://192.168.1.1:8000/', 'class' => 'col-sm-5' ) );
				$form->add( array( label => 'Admin Username', name => 'icecast-user', placeholder => 'admin', 'class' => 'col-sm-5' ) );
				$form->add( array( label => 'Admin Password', name => 'icecast-pass', placeholder => 'password', 'class' => 'col-sm-5', type => 'password' ) );
				$form->add( array( label => 'Mount Point', name => 'icecast-mount', placeholder => '/autodj', 'class' => 'col-sm-3' ) );
				$form->add( array( label => 'Fallback Mount', name => 'icecast-fallback-mount', placeholder => '/stream', 'class' => 'col-sm-3', description => '(Fallback to this mount if main has no info, not required)' ) );
				$icecast = $form->html;


				// SAM Broadcaster stats
				$form->clear();
				$form->add( array( label => 'MySQL Host', name => 'sam-host', placeholder => '127.0.0.1', 'class' => 'col-sm-5' ) );
				$form->add( array( label => 'MySQL Username', name => 'sam-user', placeholder => 'root', 'class' => 'col-sm-5' ) );
				$form->add( array( label => 'MySQL Password', name => 'sam-pass', placeholder => 'password', 'class' => 'col-sm-5', type => 'password' ) );
				$form->add( array( label => 'SAM Database', name => 'sam-db', placeholder => 'sam', 'class' => 'col-sm-3' ) );
				$sam = $form->html;


				// Centovacast stats
				$form->clear();
				$form->add( array( label => 'Centovacast URL', name => 'centova-url', placeholder => 'http://192.168.1.1:2199/', 'class' => 'col-sm-5' ) );
				$form->add( array( label => 'Centovacast Username', name => 'centova-user', placeholder => 'JohnDoe', 'class' => 'col-sm-4', description => '(Recent Tracks widget must be enabled!)' ) );
				$centova = $form->html;


				// Radionomy stats fields
				$form->clear();
				$form->add( array( label => 'Radio UID', name => 'radionomy-uid', 'class' => 'col-sm-5' ) );
				$form->add( array( label => 'Personal API Key', name => 'radionomy-apikey', 'class' => 'col-sm-5', description => '(<a href="http://board.radionomy.com/viewtopic.php?f=28&t=915&p=3105#p3105" target="_blank">Where to find these?</a>)' ) );
				$form->add( array( label => 'Track Cover', name => 'radionomy-use-cover', value => 'true', type => 'checkbox', description => 'Use Radionomy Cover Images (If Missing LastFM & Custom Uploads will be used)' ) );
				$radionomy = $form->html;


				// Custom stats
				$form->clear();
				$form->add( array( label => 'Custom URL', name => 'custom-url', placeholder => 'http://domain.com/file.php', 'class' => 'col-sm-5', description => '(Response must be plain text in format <b>Artist - Title</b>)' ) );
				$form->add( array( label => 'HTTP-Auth Username', name => 'custom-user', placeholder => 'JohnDoe', 'class' => 'col-sm-4', description => '(Optional)' ) );
				$form->add( array( label => 'HTTP-Auth Password', name => 'custom-pass', placeholder => 'Password', 'class' => 'col-sm-4', description => '(Optional)', type => 'password' ) );
				$custom = $form->html;

			?>

			var elm = $(this);
			switch ($(elm).val()) {

				case 'direct':
					$('.stats-conf').html('<?php echo $direct; ?>');
					break;

				case 'shoutcast':
					$('.stats-conf').html('<?php echo $shoutcast; ?>');
					break;

				case 'icecast':
					$('.stats-conf').html('<?php echo $icecast; ?>');
					break;

				case 'sam':
					$('.stats-conf').html('<?php echo $sam; ?>');
					break;

				case 'centovacast':
					$('.stats-conf').html('<?php echo $centova; ?>');
					break;

				case 'radionomy':
					$('.stats-conf').html('<?php echo $radionomy; ?>');
					break;

				case 'custom':
					$('.stats-conf').html('<?php echo $custom; ?>');
					break;

				default:
					$('.stats-conf').empty();
					break;

			}

			return false;

		});


		// Add stream group
		$('.addgrp').on('click', function() {

			var xid = parseInt($('.quality-group').index($(document).find('.quality-group').last())) +1 || 0;
			var qualitygroup = 'Default Quality' + ((xid >= 1) ? ' (' + (xid+1) + ')' : '') + '';

			var html = $('<div class="quality-group"><input title="Click to edit" class="input-quality" type="text" name="quality[]" value="' + qualitygroup + '">\
				<div class="pull-right"><a href="#" class="delgrp"><i class="fa fa-times"></i> Delete Group</a></div><table class="table streams"><tbody></tbody>\
				</table><a href="#" class="addrow"><i class="fa fa-plus"></i> Add More Streams</a></div>');

			$('.qualitylist').append(html);
			html.find('.addrow').trigger('click');
			return false;

		});

		// Bind delete groups
		$('.qualitylist').on('click', '.delgrp', function() {

			if ( confirm('Are you sure you wish to delete whole group?') ) {
				$(this).closest('.quality-group').remove();
			}

			// Fix indexes
			var xid = 0;
			$('.quality-group').each(function() {

				$(this).find('select, input').each(function() {

					// Change name attr via regex with its group index
					var currentName = $(this).attr('name');

					if ( currentName != null) { // Use Regex to replace index number
						$(this).attr('name', currentName.replace(/_([0-9]+)\[\]/, '_' + xid + '[]'));
					}

				});				

				xid++; // Increse counter

			});

			return false;

		});

		// Bind delete streams
		$('.qualitylist').on('click', '.remove-row', function() {

			if ( confirm('Are you sure you wish to delete this stream?') ) {
				$(this).closest('tr').remove();
			}

			return false;

		});

		// Bind add row (add streams)
		$('.qualitylist').on('click', '.addrow', function() {

			var xid = parseInt($('.quality-group').index($(this).closest('.quality-group'))) || 0;
			$(this).closest('.quality-group').find('tbody').append('<tr class="stream-row"><td class="col-sm-9">\
				<input class="form-control" type="url" placeholder="Stream URL (read above!)" name="url_' + xid + '[]"></td>\
				<td class="col-sm-2"><select name="codec_' + xid + '[]" class="form-control"><option value="mp3">MP3</option><option value="oga">OGG</option>\
				</select></td><td style="width: 5%; text-align: center;"><div class="form-control-static"><a class="remove-row" href="#" style="color: red;"><i class="fa fa-times"></i></a>\
				</div></td></tr>');

			// Re-bind custom selectboxes
			$('select').selectbox();
			return false; // Disable other actions

		});


		// Change input value for browse
		$('input[type="file"]').on('change', function() {

			var cVal = $(this).val().replace(/.*\\fakepath\\/, '');
			$(this).parent('.file-input').find('input.file-name').val(cVal);

		});


		// Delete existing logo
		$('.delete-logo').on('click', function() {

			var elm = $(this);

			// On success, delete container
			$.get('?s=channels&e=<?php echo $_GET['e']; ?>&logo=delete', function() {
				$(elm).closest('.logo-container').remove();
			});

			return false;

		});


		// Triggers
		<?php if ( isset($_POST['submit']) OR $_GET['e'] != 'add' ) echo '$(\'select#stats\').trigger(\'change\');'; ?>

		if ( $('.qualitylist .quality-group').length == false ) {
			$('.addgrp').trigger('click');
		}

	};
</script>