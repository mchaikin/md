<?php
$filex=htmlspecialchars($_GET['the_file']);
//$filex='audio/wm_follow_me.mp3';
$ext = pathinfo($filex, PATHINFO_EXTENSION);
if ($ext=='mp3' || $ext=='ogg') {
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.basename($filex).'"');
	header('Content-Length: ' . filesize($filex));
	readfile($filex);
} else {
	echo "<script>window.close();</script>";
	//die();
}
?>