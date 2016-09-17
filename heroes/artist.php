<?php
include_once ('/var/www/radio/test/connect.php');
if (isset($_GET['id'])) {
	$strpos = strpos($_GET['id'], '?ajax=1');
	if ($strpos === false) {
	include_once ('/var/www/radio/test/header.php');
	}
echo '	<div id="content">';
$id = str_replace("?ajax=1", "", $_GET['id']);
$query = mysql_query ("SELECT * FROM `heroes` WHERE `id` = '$id' LIMIT 1");
$result = mysql_fetch_array($query);
$name = $result['name'];
$about = $result['about'];
$picture = $result['picture'];
echo '		<div id="article">';
echo '			<div id="title">';
echo '				<h1>'.$name.'</h1>';
echo '			</div>';
echo '			<div id="text">'.$about.'</div>';
echo '			<div id="image">';
echo '				<img src="'.$picture.'" width="200" title="'.$name.'" tabindex="0" />';
echo '			</div>';
echo '		</div>';
echo '	</div>';
echo '</div>';
}
if ($strpos === false) {
include_once ('/var/www/radio/test/footer.php');
}
?>
</body>
</html>