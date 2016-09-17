<?php if ( $inc !== true ) { header("Location: index.php?s=home"); exit(); } ?>


<div style="display: block; margin: 10px 0;">
	<label class="control-label">Custom Player Size:</label> &nbsp;
	<input type="number" step="1" min="300" max="1000" name="width" value="720" class="form-control" style="width: 80px; display: inline-block"> x
	<input type="number" step="1" min="75" max="400" name="height" value="355" class="form-control" style="width: 80px; display: inline-block"> pixels
</div>

<div class="divider"></div>

<h2>Popup Embed Player <small><span>(720 x 355)</span></small></h2>
<p>
	This method is recommended. When player is used as popup window, it allows users to continue interacting with your web site without any problems. 
	It also ensures that the player is responsive on mobile devices and that it fits screen well. See code bellow to embed this method into your web site.
	The values written in bracket's are recommended sizes for specific player embedding type. 
	<span class="text-red">Note: You can also force specific channel via URL. Simply append <b>#channel name</b> to URL.</span>
</p>

<b>Preview 1:</b><br>
<a class="btn btn-primary launchplayer" href="#"><i class="fa fa-external-link-square"></i> Open Popup</a>

<br><br>
<b>Preview 2:</b><br>
<a href="#" class="launchplayer"><img src="./../assets/img/popup.eg.jpg" alt="Open Popup"></a>

<br><br><b>Source Code:</b><br>
<textarea class="form-control" style="width: 720px;" id="popup">
<?php echo htmlentities('<a href="#" onclick="window.open(\'http://' . $_SERVER['SERVER_NAME'] . preg_replace('!/panel/(.*)!', '/', $_SERVER['REQUEST_URI']) . '\', \'aio_radio_player\', \'width=720, height=355\'); return false;">Open Popup</a>'); ?>
</textarea>

<br><div class="divider"></div>

<h2>iFrame Embed Player <small><span>(720 x 355)</span></small></h2>
<p>
	To embed the player on any page simply use code bellow. Its very easy to deploy the player using iframe, it will work as some youtube video.
	If you want to disable auto play via URL you can append <b>?autoplay=false</b> to the URL. The values written in bracket's are recommended sizes for specific player embedding type. 
</p>

<b>Preview:</b><br>
<iframe src="./../index.php?autoplay=false" width="720" height="355" border="0" style="border: 0; box-shadow: 1px 1px 0  #fff;"></iframe>

<br><br><b>Source Code:</b><br><textarea class="form-control" style="width: 720px;" id="iframe">
<?php echo htmlentities('<iframe width="720" height="355" border="0" style="border: 0; box-shadow: 1px 1px 0  #fff;" src="http://' . $_SERVER['SERVER_NAME'] . preg_replace('!/panel/(.*)!', '/', $_SERVER['REQUEST_URI']) . '"></iframe>'); ?>
</textarea>

<div class="divider"></div>

<script type="text/javascript">

	window.loadinit = function() {

		var timeout, width = 720, height = 355;

		// Bind width input
		$('[name="width"]').on('change', function() {

			var elm = $(this);

			clearTimeout(timeout);
			timeout = setTimeout(function() {

				var tempembed = $('textarea#iframe').val();
				var temppopup = $('textarea#popup').val();

				// Set new width
				width = $(elm).val();

				// Now change elements on page
				$('iframe').width(width);
				$('textarea#iframe').val(tempembed.replace(/width=\"[0-9]+\"/, 'width="' + width + '"'));
				$('textarea#popup').val(temppopup.replace(/width=([0-9]+)/, 'width=' + width + ''));

				}, 500);
				
				return true;

		});

		// Bind height input
		$('[name="height"]').on('change', function() {

			var elm = $(this);

			clearTimeout(timeout);
			timeout = setTimeout(function() {

				var tempembed = $('textarea#iframe').val();
				var temppopup = $('textarea#popup').val();

				// Set new width
				height = $(elm).val();

				// Now change elements on page
				$('iframe').height(height);
				$('textarea#iframe').val(tempembed.replace(/height=\"[0-9]+\"/, 'height="' + height + '"'));
				$('textarea#popup').val(temppopup.replace(/height=([0-9]+)/, 'height=' + height + ''));

				}, 500);

				return true;

		});


		// Launch bind
		$('.launchplayer').on('click', function() {

			// Open popup player
			window.open('./../index.php', 'aio_radio_player', 'width=' + width + ', height=' + height);
			return false;

		});

	};

</script>