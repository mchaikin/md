<?
curl_setopt_array($ch = curl_init(), array(
CURLOPT_URL => "https://pushall.ru/api.php",
CURLOPT_POSTFIELDS => array(
    "type" => "self",
    "id" => "8274",
    "key" => "a9cd37da183d6c9d2bd07ce647c7da8e",
    "text" => $text,
    "title" => $title_pushall
  ),
  CURLOPT_RETURNTRANSFER => true
));
$return=curl_exec($ch); //получить ответ или ошибку
curl_close($ch);
?>