<?php
include_once('/var/www/radio/test/connect2.php');
if (isset($_GET['show'])) {

	//if (!isset($_GET['ajax'])) {
		include_once ('/var/www/radio/test/header.php');
	//}
	if (!preg_match("/(.+?)\D([[a-z]{4}=1)/", $_GET['show'], $mass)) {
		$show = $_GET['show'];
		}
	else {
		$show = $mass['1'];
		}
	$dir = "../audio/".$show; // Директория с mp3-файлами
	$files = glob("$dir/*.mp3"); // Получаем список mp3-файлов
	$query = mysql_query("SELECT `cover`, `title`, `hosted` FROM `Schedule` WHERE `title` LIKE '%$show%'") or die("Could not select from DB: " . mysql_error());
	$result = mysql_fetch_array($query);
	$cover = str_replace("/var/www/radio/", "http://www.musicaldecadence.ru/", $result['cover']);
	$title = $result['title'];
	$hosted = $result['hosted'];
	
if ($files != NULL) {

echo '	<div id="content">';
echo '		<div id="article">';
echo '			<script src="../../test/podcasts/player/jquery.cleanaudioplayer.js"></script>';
echo '			<div class="corner">';
echo '				<img src="'.$cover.'" alt="'.$show.'" />';
echo '			</div>';
echo '			<div id="podcast">';
echo '				<h2>'.$hosted;
echo '					<hr style="background-color: #3b6898">';
echo '					<span class="title">'.$title.'</span>';
echo '				</h2>';
echo '			</div>';
echo '		<div class="mediatec-cleanaudioplayer">';
echo '			<ul data-theme="default">';
					array_multisort(
					array_map( 'filemtime', $files ),
					SORT_NUMERIC,
					SORT_DESC,
					$files
					);
					for ($i = 0; $i < count($files); $i++) {
					$pos = strpos(basename($files[$i]), ".mp3");
					if ($pos!=false) {
						$name = substr(basename($files[$i]), 0, $pos);
						$sym = '%23';
						$link = ereg_replace('#', $sym, $files[$i]);
						preg_match('/(.+?)\s-\s(.+?)$|(.+?)\s([A-Z]{4}.+?)$/', $name, $mass);
							if ($mass[1] && $mass[2] != NULL) {
									$artist = $mass[1]; // артист
									$title = $mass[2]; // трек
							} else {
									$artist = $mass[3]; // артист
									$title = $mass[4]; // трек
									}
									
echo '				<li data-title="'.$title.'" data-artist="'.$artist.'" data-type="mp3" data-url="'.$link.'" data-free="true">1</li>'; // Выводим название файла
					}
					}  
?>
				</ul>
			</div>
			<div id="about">
				About some text
			</div>
		</div>
	</div>
</div>
<?php

if (!isset($_GET['ajax'])) {
	include_once ('/var/www/radio/test/footer.php');
}
} else {
	echo "Файлов нет";
  }
} else { header("location:index2.php");
	}
?>
</body>
</html>