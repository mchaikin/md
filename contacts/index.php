<?php
if (!isset($_GET['ajax'])) {
include_once ('/var/www/radio/test/header.php');
}
echo '	<div id="content">';
echo '		<div id="article">';
echo ' 			<h2>Эй, ты, блеать!</h2>';
echo '			<p>Хочешь стать великим дж и транслироваться на самом пиздатом радио? Тогда тебе дорога к нам, ска! Отправляй свой ссылку на свой промо микс и мы обязательно над ним поржем!</p>';
echo '			<div style="width: 50%; margin: auto;">';
echo '				<form method="POST" action="">';
echo '					<input type="text" name="name" style="width:100%" placeholder="Ваше имя"><br>';
echo '					<input type="email" name="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" style="width:100%" placeholder="E-mail"><br>';
echo '					<textarea name="link" style="width: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;" placeholder="Ссылка на микс"></textarea><br>';
echo '					<input type="submit" name="submit" value="Отправить">';
echo '				</form>';
echo '			</div>';
echo '		</div>';
echo '	</div>';
echo '</div>';
if (!isset($_GET['ajax'])) {
include_once ('/var/www/radio/test/footer.php');
}
if (isset($_POST['submit'])) {
	$peer_id = '-12515267';
	$v = '5.41';
	$accessToken = '58c9e12e57d89a7fb2ce19e0d094fd13a4cd3d6ad9dd4dfe03833ac50ae9f35a702ec60e3fdcf47646f86';
	$name = $_POST['name'];
	$email = $_POST['email'];
	$link = $_POST['link'];
	$mess = "Отправлен запрос на резидентство\n\n".$name." отправил запрос на резиденство на радио\nСсылка на микс: ".$link."\nE-mail: ".$email;
	$ch = curl_init('https://api.vk.com/method/messages.send?peer_id='.$peer_id.'&message='.urlencode($mess).'&v='.$v.'&access_token='.$accessToken);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$Exe = curl_exec($ch);
	curl_close($ch);
	if ($Exe['response']) {
		echo "<script>alert('Запрос отправлен. С Вами скоро свяжутся!')</script>";
	}
	else {
		echo "<script>alert('Запрос не отправлен. Ошибка!')</script>";
	}
}
?>