<?php
include("connect.php");
if(isset($_GET['num'])){
   $num = $_GET['num'];
   $result = mysql_query("SELECT *, DATE_FORMAT(`Date`, '%d.%m.%Y') AS 'time' FROM `News` ORDER BY `date` DESC LIMIT $num, 10"); //Вытаскиваем из таблицы 5 комментариев начиная с $num
   if(mysql_num_rows($result) > 0){         
       while($comment = mysql_fetch_array($result)) {
		$title = $comment['Name'];
		$about = $comment['About'];
		$picture = $comment['Picture'];
		$date = $comment['time'];
       
echo '			<div id="article">';
echo '				<div id="title">';
echo '					<h1>'.$title.'</h1>';
echo '				</div>';
echo '				<div id="text">'.$about.'</div>';
echo '				<div id="image">';
echo '					<img src="'.$picture.'" width="200" title="'.$title.'" tabindex="0" />';
echo '				</div>';
echo '			</div>';
echo '			<div id="footer_article">';
echo '				<div id ="date">'.$date.'<hr>';
echo '					<button>Комментарии</button>';
echo '				</div>';
echo '			</div>';
echo '			<hr>';

	   }
          sleep(1); //Сделана задержка в 1 секунду чтобы можно проследить выполнение запроса
     }else{
           echo 0; //Если записи закончились
     }
}
?>