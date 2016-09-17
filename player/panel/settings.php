<?php if ( $inc !== true ) { header("Location: index.php?s=home"); exit(); } ?>
<form method="POST" action="?s=settings">
	<?php

		// Remove empty spaces before the value
		$_POST = array_map(trim, $_POST);

		// Handle post data
		if ( isset($_POST['submit']) ) {

			if ( strlen($_POST['admin_pass']) < 5 AND !empty($_POST['admin_pass']) ) {

				echo alert ('ERROR: Panel password must have more than 5 characters.', error);

			} else if ( $_POST['admin_pass'] != $_POST['admin_pass2'] ) {

				echo alert ('ERROR: Panel passwords do not match! Please repeat same password.', error);

			} else if ( empty($_POST['admin_user']) ) {

				echo alert ('ERROR: You must enter admin username or else you won\'t be able to login to the control panel.', error);

			} else if ( !is_numeric($_POST['artist_maxlength']) OR !is_numeric($_POST['title_maxlength']) OR empty($_POST['title']) OR empty($_POST['track_regex']) ) {

				echo alert ('ERROR: Some fields are empty, please check form bellow and re-submit it!', error);

			} else if ( @preg_match("/{$_POST['track_regex']}/i", null) === false ) {

				echo alert ('ERROR: Track RegEx is invalid! Please fix it or use default value.', error);

			} else if ( !is_numeric( $_POST['stats_refresh'] ) OR $_POST['stats_refresh'] < 5 OR $_POST['stats_refresh'] > 120 ) {

				echo alert ('ERROR: Invalid range for Stats Refresh Speed. The value must not be lower than <b>5</b> and higher than <b>120</b>!', error);

			} else {

				// Delete submit key
				unset($_POST['submit'], $_POST['admin_pass2']);

				// Password handle
				if ( !empty($_POST['admin_pass']) ) { // Hash password, safety

					$_POST['admin_pass'] = hash(SHA512, $_POST['admin_pass']);

				} else { // No password provided

					$_POST['admin_pass'] = $settings['admin_pass'];

				}

				// Try to save
				if ( file_put_contents ('./../inc/conf/general.php', '<?php $settings=' . var_export($_POST, true) . '; ?>') ) {

					echo alert ('SUCCESS: Settings were successfully updated!', success);

				} else {

					echo alert ('ERROR while saving your information. Please make sure file "inc/conf/global.php" is writable (chmod 0755)!', error);

				}

			}


		} else {

			$_POST = $settings;

		}


		// Never show password
		unset($_POST['admin_pass']);
		unset($_POST['admin_pass2']);


		// Get all installed languages
		if ( is_dir('./../inc/lang') ) {
			$ff = getlist('./../inc/lang/');
			foreach ($ff as $file) { $files[$file] = strtoupper(ext_del($file)); }
		}


		// Get all available channels
		if ( is_file( './../inc/conf/channels.php' ) ) {

			include './../inc/conf/channels.php';
			$defchannels = array( 0 => 'None' );

			if ( is_array( $channels ) ) {
				foreach ( $channels as $c ) {
					$defchannels[$c['name']] = $c['name'];
				}
			}

		}


		// Init Object
		$f = new form();


		// Settings array (fields)
		$fieldsarr = array(

			// General Player Settings
			'<fieldset><legend><i class="fa fa-cogs"></i> General Settings</legend>',
			array(label => 'Player title', name => 'title', size => 64, description => '(SEO)'),
			array(label	=> 'Player description', name => 'description', type => 'textarea', description => '(SEO)'),
			array(label => 'Google Analytics', name => 'google_analytics', placeholder => 'UA-1113571-5', description => '(Tracking ID)'),
			array(label => 'Cookie(s)', name => 'cookie_support', 'class' => 'col-sm-9', value => 'true', type => 'checkbox', description => 'Use cookies to save user settings and volume permanently'),
			array(label => 'Default Language', 'class' => 'col-sm-2', name => 'default_lang', type => 'select', options => $files, description => '(Used if language is not found or Multi-language support is disabled)'),
			array(label => 'Multi-language support', name => 'multi_lang', 'class' => 'col-sm-9', 'value' => 'true', type => 'checkbox', description => ' When checked, player will support multi-languages. See Language(s) tab for management.'),
			array(label => 'Playlist(s) icon size', 'class' => 'col-sm-2', name => 'playlist_icon_size', placeholder => '32', reset => true, description => '(in pixels)'),
			array(label => 'Cache artist images', name => 'cache_artist_images', 'class' => 'col-sm-9', value => 'true', type => 'checkbox', description => ' Cache artist images on the web server (Also crop, compress and optimize images for MAX quality)'),
			array(label => 'Auto Play', name => 'autoplay', 'class' => 'col-sm-9', value => 'true', type => 'checkbox', description => ' Start playback automatically (Some devices and browsers do not support this feature)'),
			array(label => 'Initial Channel', name => 'default_channel', 'class' => 'col-sm-4', type => 'select', description => ' (Used if no cookie or hash is present)', options => $defchannels ),
			array(label => 'Facebook share (image)', 'class' => 'col-sm-5', name => 'fb_shareimg', description => '(Full URL to the image min. 200 x 200 px)'),
			array( label => 'Debug mode', 'class' => 'col-sm-4', name => 'debugging', type => 'select', options => array( 'enabled' => 'Logging only (Recommended)', 'show' => 'Enabled', 'disabled' => 'Disabled' ) ),


			// Track Information
			'</fieldset><fieldset><legend><i class="fa fa-list"></i> Track Information</legend>',
			array(label => 'Default Artist', name => 'artist_default', placeholder => 'Various Artists', 'class' => 'col-sm-4', description => '(If there is no stream information or stat\'s is not responding, this will be shown)', required => true),
			array(label => 'Default Title', name => 'title_default', placeholder => 'Unknown Track', 'class' => 'col-sm-4', description => '(If there is no stream information or stat\'s is not responding, this will be shown)', required => true),
			array(label => 'Dynamic Title', name => 'dynamic_title', 'class' => 'col-sm-9', value => 'true', type => 'checkbox', description => ' Dynamic popup window title (Show currently playing Track in window title bar)'),
			array(label => 'Artist max length', name => 'artist_maxlength', placeholder => 48, 'class' => 'col-sm-2', description => '<b>0 = disabled</b> (Maximum number of characters before shortening artist name)', required => true),
			array(label => 'Title max length', name => 'title_maxlength', placeholder => 58, 'class' => 'col-sm-2', description => '<b>0 = disabled</b> (Maximum number of characters before shortening track name)', required => true),
			'<div class="form-group"><label for="stats_refresh" class="col-sm-2 control-label">Stats refresh speed</label><div class="col-sm-2"><div class="input-append"><div class="append">sec</div><input type="stats_refresh" name="stats_refresh" class="form-control" id="stats_refresh" placeholder="15" required="" value="' . $_POST['stats_refresh'] . '"></div></div><div class="help-block">(Note: This may have big performance impact on your web server. <span class="text-red">Change with caution!</span>)</div></div>',
			array(label => 'Player API', name => 'api', 'class' => 'col-sm-9', value => 'true', type => 'checkbox', description => ' Enable support for external JSONP API requests <a target="_blank" href="https://prahec.com/project/aio-radio/docs#api">(<i class="fa fa-question-circle"></i> Documentation</a>)'),
			array(label => 'Artist/Title Regex', name => 'track_regex', 'class' => 'col-sm-5', placeholder => "(?P<artist>[^-]*)[ ]?-[ ]?(?P<title>.*)", reset => true, description => '<span class="text-red">(Only change if you know what you are doing!)</span>'),
			array(label => 'LastFM API Key', name => 'lastfm_key', 'class' => 'col-sm-4', description => '(Required if you wish to display artist images!)', placeholder => 'API Key'),


			// Panel Settings
			'</fieldset><fieldset><legend><i class="fa fa-sign-in"></i> Control Panel</legend>',
			array('label' => 'Purchase code', 'name' => 'envato_pkey', placeholder => 'Codecanyon Item Purchase code', 'description' =>	'<a target="_blank" href="https://prahec.com/envato/pkey">(<i class="fa fa-question-circle"></i> Required for updates)</a>', 'class'	=> 'col-sm-4'),
			array('label' => 'Panel username', 'name' => 'admin_user', 'class' => 'col-sm-4', required => true, placeholder => 'admin'),
			array('label' => 'Panel password', 'name' => 'admin_pass', 'class' => 'col-sm-4', 'type' => 'password', placeholder => 'min. 5 characters'),
			array('label' => 'Confirm panel password', 'name' => 'admin_pass2', 'class' => 'col-sm-4', 'type' => 'password', placeholder => 'min. 5 characters')

		);


		// Parse fields array
		foreach ( $fieldsarr as $arr ) { if ( !is_array( $arr ) ) { echo $arr; } else { echo $f->add($arr); } }

		// Rest of the form
		echo '<div class="row"><div class="col-sm-9 col-sm-offset-2">
		<b>Note</b>: You will not be able to recover password once it is set. 
		Your password will be encrypted one way (hashed).<br> To regain access please overwrite file <b>/inc/conf/general.php</b> with the original file.
		</div></div>
		</fieldset>';

	?>

	<div class="form-controls">
		<div class="row">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" name="submit" value="submit" class="btn btn-success"><i class="fa fa-pencil fa-fw"></i> Save</button>
				<a href="?s=settings" class="btn btn-danger"><i class="fa fa-times fa-fw"></i> Cancel</a>
			</div>
		</div>
	</div>

</form>