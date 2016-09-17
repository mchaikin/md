<?php if ( $inc !== true ) { header("Location: index.php?s=status"); exit; } ?>
<p>
	This player supports multi-language setup which means that (if enabled) player will automatically choose a language fit for the user's browser setting. 
	For example if the user has set language to <b>en_GB</b> language file <b>en.php</b> will be loaded (General English). You can also disable multi-language support under <b>Settings</b> tab.
	<span class="text-red">Note: All language names are in <b>ISO 639-1 </b> standard. Read more about this standard here: 
		<a target="_blank" href="http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes">http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes</a></span>
</p>


<?php

	// Display languages table
	if ( !isset($_GET['add']) AND empty($_GET['edit']) ) {

		// File delete handler
		if ( isset($_GET['del']) ) { 

			$_GET['del'] = preg_replace('![^a-z0-9]!i', '', $_GET['del']); ## Replace all but characters and numbers
			if ( is_file('./../inc/lang/' . $_GET['del'] . '.php') && unlink('./../inc/lang/' . $_GET['del'] . '.php') === true) {

				echo alert('Successfully deleted language!', success);

			} else {

				echo alert('ERROR occured while attempting to delete language file!', error);

			}

		}


		// Read language directory for all files
		$files = getlist('./../inc/lang/');


		// If less then one result
		if ( count($files) < 1 ) {

			echo alert ('No language files found! If you deleted en.php by mistake, please re-upload it from the original ZIP file.');

		} else { // Else

			if ( !empty($_SESSION['msg']) ) { // Display message from session
				echo $_SESSION['msg']; unset($_SESSION['msg']);
			}

		?>

		<table class="table hover">
			<thead>
				<tr>
					<th>Language name</th>
					<th>Actions</th>
				</tr>
			</thead>

			<tbody>

				<?php

					foreach ($files as $file) {

						echo '<tr><td class="col-sm-9">' . strtoupper(ext_del($file)) . '</td>
						<td><a class="btn btn-primary btn-small" href="?s=lang&edit=' . ext_del($file) . '"><i class="fa fa-edit"></i> Edit</a> ' . ( ($file != 'en.php') ? ' 
							<a class="btn btn-danger btn-small" onclick="return confirm(\'Are you sure?\');" href="?s=lang&del=' . ext_del($file) . '"><i class="fa fa-times"></i> Delete</a>' : '') . '</td>
						</tr>';

					}
				?>

			</tbody>
		</table>

		<a href="?s=lang&add" class="btn btn-success"><i class="fa fa-plus-circle"></i> Add Language</a>
		<?php
		}

	} else {


		// Remove empty spaces before the value
		$_POST = array_map(trim, $_POST);

		// Handle submission
		if ( isset($_POST['submit']) ) {

			$file = preg_replace('![^a-z0-9]!i', '', ((empty($_POST['isocode'])) ? $_GET['edit'] : $_POST['isocode']));
			unset($_POST['isocode'], $_POST['submit']);

			if ( empty($file) ) {

				echo alert('Invalid ISO code or file name, please cancel and try again.', error);

			} else {			

				// Try to save
				if ( file_put_contents ('./../inc/lang/' . $file . '.php', '<?php $lang=' . var_export($_POST, true) . '; ?>') ) {

					$_SESSION['msg'] = alert('Language file ' . ((isset($_GET['edit'])) ? 'updated' : 'added') . ' successfully!', success);
					header('Location: ?s=lang');
					exit;

				} else {

					alert('ERROR while saving your information. Please make sure directory "/inc/lang/" is writable (chmod 0755)!', error);

				}

			}

		}

		// Load existing file
		if ( isset($_GET['edit']) && !isset($_POST['submit']) ) {

			$_GET['edit'] = preg_replace('![^a-z0-9]!i', '', $_GET['edit']); ## Replace all but characters and numbers
			if ( is_file('./../inc/lang/' . $_GET['edit'] . '.php') ) {

				include './../inc/lang/' . $_GET['edit'] . '.php';
				$_POST = $lang;

			} else {

				header ("Location: ?s=lang");

			}

		}

		echo '<form action="?s=lang&' . ((isset($_GET['add'])) ? 'add' : 'edit=' . $_GET['edit']) . '" method="POST">';

		$f = new form();
		if ( isset($_GET['add']) ) { echo $f->add(array(label => 'Language ISO code', placeholder => 'en', name => 'isocode', 'class' => 'col-sm-2', description => '(ISO 639-1 Langage code)')); } 
		else { echo $f->add(array(label => 'Language ISO code', type => 'static', value => '<b>' . strtoupper($_GET['edit']) . '</b>' )); }

		echo $f->add(array(label => 'Loading Message', placeholder => 'Loading, please wait...', name => 'loading-message', reset => true));

		echo '<div class="divider"></div>';
		echo $f->add(array(label => 'Settings', placeholder => 'Select stream quality', name => 'ui-settings', reset => true));
		echo $f->add(array(label => 'Channels List', placeholder => 'Channels list', name => 'ui-channels', reset => true));
		echo $f->add(array(label => 'Play Button', placeholder => 'Start playing', name => 'ui-play', reset => true));
		echo $f->add(array(label => 'Stop Button', placeholder => 'Stop playing', name => 'ui-stop', reset => true));
		echo $f->add(array(label => 'Volume Circle', placeholder => 'Drag to change volume', name => 'ui-volume-circle', reset => true));
		echo $f->add(array(label => 'Playlists Text', placeholder => 'Listen in your favourite player', name => 'ui-playlists', reset => true));

		echo '<div class="divider"></div>';
		echo $f->add(array(label => 'Status: Loading', placeholder => 'Loading {STREAM}...', name => 'status-init', reset => true, 'class' => 'col-sm-4', description => '({STREAM} will be replaced by current channel name)'));
		echo $f->add(array(label => 'Status: Playing', placeholder => 'Playing {STREAM}...', name => 'status-playing', reset => true, 'class' => 'col-sm-4', description => '({STREAM} will be replaced by current channel name)'));
		echo $f->add(array(label => 'Status: Stopped', placeholder => 'Player stopped.', name => 'status-stopped', reset => true, 'class' => 'col-sm-4'));
		echo $f->add(array(label => 'Status: Volume', placeholder => 'Volume: {LEVEL}', name => 'status-volume', reset => true, 'class' => 'col-sm-4', description => '({LEVEL} will be replaced by current volume level)'));
		echo $f->add(array(label => 'Status: Muted', placeholder => 'Player muted.', name => 'status-muted', reset => true, 'class' => 'col-sm-4'));

		echo '<div class="divider"></div>';
		echo $f->add(array(label => 'Share', placeholder => 'Share', name => 'share', reset => true, 'class' => 'col-sm-4'));
		echo $f->add(array(label => 'Twitter Post', placeholder => 'I am listening to {TRACK}!', name => 'twitter-share', reset => true, 'class' => 'col-sm-7', description => '({TRACK} will be replaced by current playing track)'));

		echo '
		<div class="form-controls"><div class="row">
		<div class="col-sm-offset-2 col-sm-10">
		<button type="submit" value="save" name="submit" class="btn btn-success"><i class="fa fa-pencil fa-fw"></i> Save</button>
		<a href="?s=lang" class="btn btn-danger"><i class="fa fa-times fa-fw"></i> Cancel</a>
		</div></div></div>
		</form>';



	}

?>