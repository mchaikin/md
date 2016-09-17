<?php
include_once ('/var/www/radio/test/connect.php');

if (!isset($_GET['ajax'])) {
include_once ('/var/www/radio/test/header.php');
}
echo '	<div id="content">';
echo '<center><iframe width="720" height="350" border="0" style="border: 0px solid #e9edf1; margin: 70px auto;" src="http://musicaldecadence.ru/test/player/index3.php"></iframe></center>';
echo '		</div>';
echo '</div>';
if (!isset($_GET['ajax'])) {
include_once ('/var/www/radio/test/footer.php');
}
?>