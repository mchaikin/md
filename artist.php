<?php

if (isset($_GET['show'])) {

  $dir = "audio/".$_GET['show']; // ���������� � mp3-�������
  $files = glob("$dir/*.mp3"); // �������� ������ mp3-������
  for ($i = 0; $i < count($files); $i++) {

    $pos = strpos(basename($files[$i]), ".mp3");
	if ($pos!=false)
 	{
    $name = substr(basename($files[$i]), 0, $pos);
    echo "<p>".$name."</p>"; // ������� �������� �����
}
    echo "<audio controls='controls'>"; // ������� ��� ����� � ������� ����������
	$sym = '%23';
	$link = ereg_replace('#', $sym, $files[$i]);
    echo "<source type='audio/mpeg' src='".$link."' />"; // ���������� ���� � �����-�����
    echo "</audio>"; // ��������� ���
    echo "<br><br>"; // ��������� �� 2 �������� �� ����� ������
  }
}
else { header("location:shows.php");
}
?>