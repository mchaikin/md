<?php if ( $inc !== true ) { header("Location: index.php?s=home"); exit; } ?>
<p>
	AIO - Radio Station Player supports multi-channel configuration(s) but If a single channel is configured or a single stream, player will hide the unused buttons.
	Other settings that affect all channels are covered in <b>Settings tab</b>.
	<?php if ( isset($_GET['e']) ) echo '<span class="text-red">Please read instructions carefully! Invalid configuration could cause player to stop working properly.</span>'; ?>
</p>
<?php

	// Load channels file (used for all actions on this page)
	if ( is_file("./../inc/conf/channels.php") ) { include ("./../inc/conf/channels.php"); }
	if ( !is_array($channels) ) $channels = array();


	// Not edit/add/del action, show table of channels
	if ( !isset($_GET['e']) OR $_GET['e'] == 'del' ) {

		// Display message from session
		if ( !empty($_SESSION['msg']) ) {
			echo $_SESSION['msg']; unset($_SESSION['msg']);
		}

		// Delete channel, by key
		if ( $_GET['e'] == 'del' ) {

			if ( !is_array($channels[$_GET['id']]) ) {

				echo alert ('Sorry but selected channel does not exist, so not removed.', error);

			} else {

				// Attempt to delete Logo of channel
				if ( is_file($channels[$_GET['id']]['logo']) ) {
					@unlink($channels[$_GET['id']]['logo']);
				}

				unset ($channels[$_GET['id']]); ## Delete by key
				if ( !file_put_contents('./../inc/conf/channels.php', '<?php $channels = ' . var_export($channels, true) . '; ?>') ) { // Attempt to save

					echo alert ('Unable to save configuration into file.<br>Please make sure that file "/inc/conf/channels.php" is writable (chmod 755)!', error);

				} else {

					echo alert ('Channel was successfully deleted.', success);

				}

			}

		}
	?>

	<table class="table hover">
		<thead>
			<tr>
				<th class="col-sm-3">Channel Name</th>
				<th>Theme</th>
				<th>Last Cache Entry</th>
				<th>Info Type</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
			<?php

				if ( count($channels) <= 0 ) {

					echo alert ('You have not yet configured any channels, please do that first.');

				} else {

					// Loop through channels
					foreach ($channels as $key => $channel) {

						// Count streams
						$sc = 0;
						foreach ($channel['streams'] as $stream) {
							$sc = $sc + count($stream);
						}

						// Make expected files look nicer
						switch ( $channel['skin'] ) {
							case 'basic.style.css':	$skinname = 'Default Light'; break;
							case 'basic.dark.css':	$skinname = 'Default Light'; break;
							case 'html5-radio.css':	$skinname = 'HTML5 Player'; break;
							default: $skinname = ucfirst( ext_del( $channel['skin'] ) ); break;
						}
 
						// Check for last cached entry
						if ( is_file("./../tmp/cache/stream.{$key}.info.cache") ) {
							$cached = unserialize(file_get_contents("./../tmp/cache/stream.{$key}.info.cache"));
						}

						echo '<tr>
						<td>' . $channel['name'] . '</td>
						<td>' . $skinname . '</td>
						<td><i>' . shorten($cached['artist'] . ' - ' . $cached['title'], 60) . '</i></td>
						<td>' . ((!empty($channel['stats']['method']) && $channel['stats']['method'] != 'disabled' ) ? ucfirst($channel['stats']['method']) : 'Disabled' ) . '</td> 
						<td><a class="btn btn-primary btn-small" href="?s=channels&e=' . $key . '"><i class="fa fa-edit"></i> Edit</a>
						<a class="btn btn-danger btn-small" onclick="return confirm(\'Are you sure?\');" href="?s=channels&e=del&id=' . $key . '"><i class="fa fa-times"></i> Delete</a></td>			
						</tr>';


					}	

				}	

			?>
		</tbody>	
	</table>


	<a class="btn btn-success" href="?s=channels&e=add"><i class="fa fa-plus"></i> Add Channel</a>

	<?php	

	} else {

		include 'channels.edit.php';

	}

?>