<?php
include_once ('/var/www/radio/test/connect.php');
if (!isset($_GET['ajax'])) {
include_once ('/var/www/radio/test/header.php');
}
if (isset($_GET['id'])) {
$id = $_GET['id'];
echo '	<div id="content">';
$query = mysql_query ("SELECT *, DATE_FORMAT(`Date`, '%d.%m.%Y') AS 'time' FROM `News` WHERE `id` = '$id'");
$result = mysql_fetch_array($query);
$title = $result['Name'];
$about = $result['About'];
$picture = $result['Picture'];
$date = $result['time'];
$audio = $result['Audio'];
echo '		<div id="article">';
echo '			<div id="title">';
echo '				<h1>'.$title.'</h1>';
echo '			</div>';
echo '			<div id="text">'.$about;
if ($audio !== NULL) {
echo '				<script src="../podcasts/player/jquery.cleanaudioplayer.js"></script>';
echo '				<br><div class="mediatec-cleanaudioplayer">';
echo '					<ul data-theme="default">';
echo '						<li data-title="" data-artist="" data-type="mp3" data-url="'.$audio.'" data-free="true">1</li>'; 
echo '					</ul>';
echo '				</div>';
}
echo '			</div>';
echo '			<div id="image">';
echo '				<img src="'.$picture.'" width="200" title="'.$title.'" tabindex="0" />';
echo '			</div>';
echo '		</div>';
echo '		<div id="footer_article">';
echo '			<div id ="date">'.$date.'<hr>';
echo '			</div>';
echo '			<div id="vk_comments"></div>';
echo '				<script type="text/javascript">';
echo '					VK.Widgets.Comments("vk_comments", {limit: 5, width: "800", attach: "*"});';
echo '				</script>';
echo '		</div>';
echo '	</div>';
echo '</div>';
}
if (!isset($_GET['ajax'])) {
include_once ('/var/www/radio/test/footer.php');
}
?>