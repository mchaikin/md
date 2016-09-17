<?php if ( $inc !== true ) { header("Location: index.php?s=home"); exit(); }

	if ( !is_writable('./../tmp/updates') ) {
		echo alert ('Directory <b>/tmp/updates/</b> is not writable! This means that player will not be able to download update files!
		<br>You can fix this issue by setting <b>chmod</b> of folder <b>/tmp/updates/</b> to <b>755</b>.');
	}

?>
<div class="update-content">
	<div class="text-center">
		<br /><b>Loading, please wait...</b>
		<br><img src="./../assets/img/preloader.svg" width="48" height="48" alt="preloader">
	</div>
</div>

<div id="changelog" style="display: none; margin-top: 25px;">
	<h5>Update History</h5>
	<div class="commands-pre">
		<pre class="latest-changelog">
			Loading...
		</pre>
	</div>
</div>

<iframe id="update" style="border: 0; width:0px; height:0px;" width="0" height="0" src="about:blank" border="0"></iframe>

<script type="text/javascript">

	window.loadinit = function() {

		$.ajax({

			dataType: 'jsonp',
			timeout: 3500,
			url: 'https://prahec.com/envato/update?action=check&itemid=<?php echo $item; ?>&method=jsonp&callback=?',
			success: function(data) {

				if ( version == null ) {

					$('.update-content').html('<?php echo alert('Failed to check for updates! Unable to determine script version.', error); ?>');

				} else if ( data.version == null ) {

					$('.update-content').html('<?php echo alert('<h5>Unavailable!</h5>Sorry but there are no updates available, please check again later.'); ?>');

				} else if ( parseFloat(data.version) <= parseFloat(version) ) {

					$('.update-content').html('<?php echo alert('<h5>No update available.</h5>You are already running the latest script version, please check again later.', info); ?>'); 

				} else {

					$('.update-content').html('<h5>Update available!</h5>\
						<p>There is an update available and to ensure latest bug fixes, security improvements, best possible performance and best compatibility with various streaming providers it is recommended \
						to update the script to latest version. Available update version <b>' + data.version + '</b> was released on <b>' + data.release + '</b>. <span class="text-red">Please before you proceed create a backup, \
						just in case something goes wrong! <br><i>Note: Updates usually include various style sheet (CSS) fixes and changes, so it might be neccesary to re-compile your color schemes!</i>\
						</span></p><a href="#" class="btn btn-success init-update"><i class="fa fa-cloud-download"></i> Update now</a>');

				}


				// At end, append changelog if available
				if ( data.changelog != null ) {

					// Add background color to messages fixed, changed, etc...
					var colours = { 'Fixed': '#27ae60', 'Changed': '#f1c40f', 'Added': '#e67e22', 'Disabled': '#e74c3c', 'Removed': '#e74c3c', 'Improved': '#34495e', 'Updated': '#3498db' };
					$.each(colours, function(key, color) {
						
						var re = new RegExp("- " + key + "", "g");
						data.changelog = data.changelog.replace(re, '<span style="display: inline-block; color: #fff; text-align: center; margin: 1px 0; \
							padding: 0px 4px; width: 65px; border-radius: 2px; background-color: ' + color + ';">' + key + '</span>');
							
					});
					
					// Tweak Update Name
					data.changelog = data.changelog.replace(/Update (.*):\n/gi, "<div class=\"divider\"></div><b style=\"font-size: 14px; font-weight: bold;\">Update $1</b>\n");

					// Append to DOM		
					$('.latest-changelog').html(data.changelog);

				} else {

					$('.latest-changelog').html('Sorry, latest change log is unavailable.');

				}

				// Show change log
				$('#changelog').show();

				// Bind UPDATE button
				$('.init-update').on('click', function() {

					// Delete useles stuff
					$('.update-content').remove();
					$('#changelog').hide().after('<h5>Update Log</h5><pre class="update-text commands-pre">Update process started, please wait...</pre>');
					$(this).remove();

					// Change Iframe target
					$('iframe#update').attr('src', 'iframe.update.php?start=true&itemid=<?php echo $item; ?>');

					return false;	

				});

			}

		}).fail( function( xhr, status ) {

			$('.update-content').html('<?php echo alert('Connection to the update server has failed! See the details bellow: <pre id="ajax-error"></pre>', error); ?>');

			switch ( status ) {

				case 'timeout': var loggie = 'Unable to connect to the update server. Please check again later!'; break;
				default: var loggie = status; break;

			}

			$('pre#ajax-error').html( loggie );

		});

	};

</script>