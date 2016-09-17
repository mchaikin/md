<?php
include_once ('/var/www/radio/test/connect.php');

if (!isset($_GET['ajax'])) {
include_once ('/var/www/radio/test/header.php');
}
echo '	<div id="content">';
$query = mysql_query ("SELECT *, DATE_FORMAT(`Date`, '%d.%m.%Y') AS 'time' FROM `News` ORDER BY `date` DESC LIMIT 10");
while ($result = mysql_fetch_array($query)) {
$id = $result['ID'];
$title = $result['Name'];
$about = $result['About'];
$picture = $result['Picture'];
$date = $result['time'];
echo '		<div id="article">';
echo '			<div id="title">';
echo '				<h1>'.$title.'</h1>';
echo '			</div>';
echo '			<div id="text">'.$about.'</div>';
echo '			<div id="image">';
echo '				<img src="'.$picture.'" width="200" title="'.$title.'" tabindex="0" />';
echo '			</div>';
echo '		</div>';
echo '		<div id="footer_article">';
echo '			<div id ="date">'.$date.'<hr>';
echo '						<button onclick="window.location.href=\'/test/article/?id='.$id.'\'">Комментарии</button>';
//echo '						<a href="article/index.php?id='.$id.'">Комментарии</a>';
echo '			</div>';
echo '		</div>';
echo '		<hr>';
}
echo '	</div>';
echo '		<div id="load">';
echo '			<script src="../test/conf/podgruzka.js"></script>';
echo '			<img src="../test/img/loading.gif" id="imgLoad">';
echo '			<div>Загрузить еще</div>';
echo '		</div>';
echo '</div>';
if (!isset($_GET['ajax'])) {
include_once ('/var/www/radio/test/footer.php');
}
?>