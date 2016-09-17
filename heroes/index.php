<?php
include_once ('/var/www/radio/test/connect.php');
	if (!isset($_GET['ajax'])) {
	include_once ('/var/www/radio/test/header.php');
	}
echo '	<div id="content">';
echo '		<div id="article">';
echo '			<div id="wrap">';
echo '				<ul id="gallery">';
$query = mysql_query ("SELECT * FROM `country` ORDER BY `country` ASC");
while ($result = mysql_fetch_array($query)) {
$id = $result['id'];
$country = $result['country'];
$flag = $result['flag'];
	
echo '					<li class="li">';
echo '						<a href="list.php?country='.$country.'">';
echo '							<img class="cover" src="http://musicaldecadence.ru/test'.$flag.'"/>';
echo '							<div>'.$country;
//echo '								<span class="hosted"><br>by '.$artist.'</span>';
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