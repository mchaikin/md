<?php
include_once ('/var/www/radio/test/connect2.php');
if (!isset($_GET['ajax'])) {
	include_once ('/var/www/radio/test/header.php');
}
echo '	<div id="content">';
echo '		<div id="article">';
echo '			<div id="wrap">';
echo '				<ul id="gallery">';
$query = mysql_query ("SELECT `title`, `cover`, `hosted` FROM `Schedule` WHERE `weekday` NOT LIKE '%_pause%' GROUP BY `title` ORDER BY `title` ASC");
while ($result = mysql_fetch_array($query)) {
$title = $result['title'];
$cover = str_replace("/var/www/radio/", "../../", $result['cover']);
$artist = $result['hosted'];
	
echo '					<li class="li">';
echo '						<a href="artist.php?show='.$title.'">';
echo '							<img class="cover" src="'.$cover.'"/>';
echo '							<div>'.$title;
echo '								<span class="hosted"><br>by '.$artist.'</span>';
echo '							</div>';
echo '						</a>';
echo '					</li>';
}
echo '				</ul>';
echo '			</div>';
echo '		</div>';
echo '	</div>';
echo '</div>';
if (!isset($_GET['ajax'])) {
	include_once ('/var/www/radio/test/footer.php');
}
?>
</body>
</html>