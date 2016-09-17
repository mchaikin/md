<?php
include_once ('/var/www/radio/test/connect.php');
if (isset($_GET['country'])) {
	$strpos = strpos($_GET['country'], '?ajax=1');
	if ($strpos === false) {
	include_once ('/var/www/radio/test/header.php');
	}
echo '	<div id="content">';
echo '		<div id="article">';
echo '			<div id="wrap">';
echo '				<ul id="gallery">';
$country = str_replace("?ajax=1", "", $_GET['country']);
$query = mysql_query ("SELECT `id`, `name`, `picture` FROM `heroes` WHERE `country` = '$country' ORDER BY `name` ASC");
while ($result = mysql_fetch_array($query)) {
$id = $result['id'];
$name = $result['name'];
$picture = $result['picture'];
echo '					<li class="li">';
echo '						<a href="artist.php?id='.$id.'">';
echo '							<img class="cover" src="'.$picture.'"/>';
echo '							<div>'.$name;
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
} 
if ($strpos === false) {
	include_once ('/var/www/radio/test/footer.php');
	}
?>
</body>
</html>