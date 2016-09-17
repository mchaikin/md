<?php

if (isset($_GET['show'])) {

  $dir = "audio/".$_GET['show']; // Директория с mp3-файлами
  $files = glob("$dir/*.mp3"); // Получаем список mp3-файлов
  for ($i = 0; $i < count($files); $i++) {

    $pos = strpos(basename($files[$i]), ".mp3");
	if ($pos!=false)
 	{
    $name = substr(basename($files[$i]), 0, $pos);
    echo "<p>".$name."</p>"; // Выводим название файла
}
    echo "<audio controls='controls'>"; // Выводим тег аудио с панелью управления
	$sym = '%23';
	$link = ereg_replace('#', $sym, $files[$i]);
    echo "<source type='audio/mpeg' src='".$link."' />"; // Подключаем путь к аудио-файлу
    echo "</audio>"; // Закрываем тег
    echo "<br><br>"; // Переходим на 2 перехода на новую строку
  }
}
else { header("location:shows.php");
}
?>