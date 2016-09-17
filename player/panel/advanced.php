<?php if ( $inc !== true ) { header("Location: index.php?s=home"); exit; }

	// Load channels file (used for all actions on this page)
	if ( is_file("./../inc/conf/channels.php") ) { include ("./../inc/conf/channels.php"); }
	if ( !is_array($channels) ) $channels = array();

?>
<legend><i class="fa fa-medkit"></i> Connection Test & Debug</legend>
<p>
	This option will allow you to test if required ports are being blocked by a firewall. 
	In case connection fails you will have to contact web hosting provider to unblock specific ports.
</p>

<div class="row">
	<div class="col-sm-4" style="padding-right:5px;">
		<select class="form-control" name="debug-server">
			<?php echo ((count($channels) >= 1) ? '<option value="user">Test Configured Channels</option>' : ''); ?>
			<option value="all">Test ports 8000, 2199 and 80</option>
			<option value="ports">Shoutcast/Icecast port 8000</option>
			<option value="centovacast">Centovacast Port 2199</option>
			<option value="radionomy">Radionomy API test</option>
		</select>
	</div>
	<button class="btn btn-primary start-debug"><i class="fa fa-play"></i> &nbsp;Start Test</button>
</div>

<pre class="debug-output commands-pre" style="display: none; margin-bottom: 0;"></pre>
<iframe id="debug-iframe" src="about:blank" style="border:0;" border="0" width="0" height="0"></iframe>

<legend><i class="fa fa-folder"></i> Custom Color Scheme(s)</legend>
<p>
	This option allows you to create your own color scheme for the player. 
	The generated color scheme will be saved as <b>theme-name.css</b> file under <b>/assets/css/</b> directory.
</p>

<?php

	// Handle compiling new CSS stylesheet
	if ( $_POST['submit'] == 'compile' ) {

		include 'scss.lib.inc.php';

		if ( empty($_POST['filename']) OR empty($_POST['base-theme']) OR empty($_POST['base-color']) ) {

			echo alert ('Invalid data submission! There are some missing fields, please try again!', error);

		} else if ( !is_file("./../assets/scss/{$_POST['base-theme']}") ) {

			echo alert ('Unable to compile new theme since the <b>base theme</b> file is missing!', error);

		} else {

			// Compile SASS and save it as file.
			$scss = new scssc();
			$scss->setImportPaths("./../assets/scss/");
			$scss->setFormatter('scss_formatter_compressed');

			// Compile!
			$contents = $scss->compile('$bg-color: ' . $_POST['base-color'] . '; @import \'' . $_POST['base-theme'] . '\';');
			
			// Append color & scheme to the output file so we can use the information on update
			if ( !empty( $contents ) ) {
				
				// Replace pre-defined text strings
				$contents = preg_replace( array( '/color=([^;]*);/i', '/scheme=([^;]*);/i' ), array( "color={$_POST['base-color']};", "scheme={$_POST['base-theme']};" ), $contents );
				
			}

			// Attempt to save
			if ( file_put_contents("./../assets/css/{$_POST['filename']}.css", $contents) ) {

				echo alert ('Successfully compiled new player theme!', success);

			} else {

				echo alert ('Unable to save new theme! Please make sure directory <b>/assets/css/</b> is writable (chmod 755)!', error);

			}

		}

	}

?>

<form method="POST" action="?s=advanced">

	<div class="form-group">
		<label class="col-sm-2 control-label" for="filename">New theme name:</label>
		<div class="col-sm-4">
			<input class="form-control" type="text" name="filename" placeholder="base.color" value="" id="filename" required>
		</div>
		<div class="help-block"> (If you enter name of existing theme, this will overwrite it)</div>
	</div>

	<div class="form-group">
		<label class="col-sm-2 control-label" for="base-theme">Select base theme:</label>
		<div class="col-sm-4">
			<select class="form-control" name="base-theme" id="base-theme">
				<option value="basic.style.scss">Default Light</option>
				<option value="basic.dark.scss">Default Dark</option>
			</select>
		</div>
		<div class="help-block"> (Selected theme will be used as a "base" for the new color scheme)</div>
	</div>

	<div class="form-group">
		<label class="col-sm-2 control-label" for="basecolor">Select base color:</label>
		<div class="col-sm-4">
			<input id="basecolor" type="color" value="#2196f3" default="#2196f3" name="base-color" required>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" name="submit" value="compile" class="btn btn-success"><i class="fa fa-pencil fa-fw"></i> Compile</button>
		</div>
	</div>

</form><br>

<legend><i class="fa fa-users"></i> Manage Artist(s) Images</legend>
<p>
	This option allows you to set your own images for various artists. These images also have higher priority over LastFM or any other API's.
</p>
<?php

	// Extensions to allow
	$allowext = array ('jpeg', 'png', 'gif', 'jpg');

	// Short function to delete all extensions for artist
	function delartist ($name) {

		global $allowext;

		// Set variables
		$files 	= getlist('./../tmp/images/');
		$name 	= artistname($name);

		// If name is default, skip
		if ( $name == 'default' ) { return false; }

		// If is array, loop through files and match what we're deleting
		if ( is_array($files) ) {

			foreach ($files as $file) { // Loop files

				if ( ext_del($file) == $name ) { // File matches, delete all extensions of this artist
					foreach ($allowext as $ext) { @unlink("./../tmp/images/" . ext_del($file) . ".{$ext}"); } ## Delete
					return true; // stop loop
				} // End match

				/* If not matched, just continue. no other work to be done.
				============================================================= */
			}

		}	


	}


	// Handle deletions
	if ( !empty($_GET['delete-artist']) ) {
		delartist($_GET['delete-artist']);
	}


	// Handle artist image upload
	if ( $_POST['submit'] == 'upload' ) {

		if ( !in_array(ext_get($_FILES['image']['name']), $allowext) ) {

			echo alert ('You have uploaded invalid image file!', error);

		} else if ( empty($_POST['artist']) ) {

			echo alert ('You need to enter artist name!', error);

		} else {

			$artist = artistname($_POST['artist']);
			delartist($_POST['artist']);

			// Attempt to save
			$up = upload('image', './../tmp/images/', $artist);
			if ( !is_array($up) ) {

				echo alert("Uploading failed! ERROR: {$up}", error);

			} else {

				echo alert ('Artist was added successfully!', success);

				// Crop
				$img = new image ($up['path']);
				$img->resize("280x280", 'crop', array(cropY => $_POST['cropY'], cropX => $_POST['cropX']));
				$img->save($up['path']);

			}

		}

	}




	$files = getlist('./../tmp/images/');
	if ( !is_array($files) ) {

		echo alert ('Sorry, no images found. Images will appear after you upload them.'); 

	} else {

		echo '<table class="table hover"><thead><tr>
		<th class="col-sm-6">Artist Name</th>
		<th>Image Path</th><th>File size</th>
		<th>Action</th></tr></thead><tbody>';

		foreach ( $files as $file ) {

			// Skip logo files
			if ( preg_match('/logo\.[0-9]+/i', $file) ) { continue; }

			// Path, table
			$path = 'tmp/images/' . $file;
			echo '<tr><td><img style="float: left;" width="22" height="22" src="./../' . $path . '"> &nbsp; <span class="artist-name">' . ext_del($file) . '</span></td><td>' . $path . '</td>
			<td>' . file_size( filesize("./../{$path}") ) . '</td>
			<td><a href="#" class="edit-img btn btn-primary btn-small"><i class="fa fa-edit"></i> Edit</a> 
			' . (( ext_del($file) != 'default' ) ? '<a href="#" class="delete-img btn btn-danger btn-small"><i class="fa fa-times"></i> Delete</a>' : '') . '</td>
			</tr>';

		}	

		echo '</tbody></table>';

	}

?>

<h5>Upload artist image</h5>
<p>
	Artist name will not be preserved because some filesystems do not support special characters. <br>
</p>
<form method="POST" action="?s=advanced" enctype="multipart/form-data">

	<div class="form-group">
		<label class="col-sm-2 control-label" for="artist" style="width: auto; text-align: left;">Artist Name:</label>
		<div class="col-sm-4">
			<input class="form-control" type="text" name="artist" placeholder="David Guetta" value="" id="artist">
		</div>
		<div class="help-block"> (Artist images are matched literally. E.g.: David Guetta will not match artist name Dj David Guetta!)</div>
	</div>


	<div class="form-group">
		<label class="col-sm-2 control-label" for="artist-image" style="width: auto; text-align: left;">Artist Image</label>
		<div class="col-sm-8">
			<div class="file-input">

				<input type="file" id="artist-image" name="image">

				<div class="input-group col-sm-5">
					<input type="text" class="form-control file-name" placeholder="Select an image">
					<div class="input-group-btn">
						<a href="#" class="btn btn-info"><i class="fa fa-folder-open fa-fw"></i> Browse</a>
					</div>
				</div>
			</div>

			<div class="croparea"><label for="artist-image" style="display: block; text-align: center;"><i class="fa fa-image" style="font-size: 30px; padding:55px 0; color: #E0E0E0;"></i></label></div>
			<input type="hidden" name="cropX" value="0">
			<input type="hidden" name="cropY" value="0">

			<i>JPEG, JPG, PNG and GIF accepted. <br>If image aspect ratio doesn't fit, you can move the crop area.</i>
		</div>
	</div>

	<button type="submit" name="submit" value="upload" class="btn btn-info"><i class="fa fa-cloud-upload"></i> Upload</button>

</form>

<script>
	window.loadinit = function() {

		// Bind debug button
		$('.start-debug').on('click', function() {

			var elm = $(this);
			$('.debug-output').show().html('<b>Connecting, please wait...</b>');
			$('#debug-iframe').attr('src', 'iframe.debug.php?test=' + $('[name="debug-server"]').val());

			return false;

		});


		// Artist change
		$("input[type='file']").on("change", function() {

			// Change form input
			var cVal = $(this).val().replace(/.*\\fakepath\\/, '');
			$(this).parent('.file-input').find('input.file-name').val(cVal);

			// Preview image and crop area
			var url = $(this).val();
			var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();

			if (this.files && this.files[0]&& (ext == "gif" || ext == "png" || ext == "jpeg" || ext == "jpg")) {

				var reader = new FileReader();
				var image = new Image();

				reader.onload = function (e) {

					image.src = e.target.result;
					image.onload = function() {
						$('.croparea').imagearea(this.src, { width: 140, height: 140 });
					};
				}

				reader.readAsDataURL(this.files[0]);

			}
		});


		// Bind edit
		$('.edit-img').on('click', function() {

			var artistname = $(this).closest('tr').find('.artist-name').text();
			$('input#artist').val(artistname).focus();

			return false;
		});


		// Bind delete
		$('.delete-img').on('click', function() {

			var artistname = $(this).closest('tr').find('.artist-name').text(), elm = $(this);
			$.get('?s=advanced&delete-artist=' + artistname, function() {
				$(elm).closest('tr').remove();				
			});

			return false;
		});

	};
</script>
<script type="text/javascript" src="./../assets/js/jquery.imagecrop.min.js"></script>
<script type="text/javascript" src="https://cdn.prahec.com/js/spectrum.min.js"></script>
<script type="text/javascript">
	$("#basecolor").spectrum({
		preferredFormat: "hex",
		showPalette: true,
		hideAfterPaletteSelect:true,
		showInput: true,
		palette: [
			['#16a085', '#27ae60', '#2196f3', '#9b59b6'],
			['#34495e', '#f39c12', '#d35400', '#c0392b'],
			['#585858']
		]
	});
</script>
<link href="https://cdn.prahec.com/css/spectrum.min.css" rel="stylesheet" type="text/css">