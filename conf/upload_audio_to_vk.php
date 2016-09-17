<?
//Получаем сервер для загрузки
$serv = $vk->api('audio.getUploadServer');
foreach($serv as $x1)
{
    if (is_array($x1))
        foreach ($x1 as $url1)
            $url = $url1;
    else
        $x = $x1;
}
//Спим секунду
sleep(1);
//Загружаем аудио и получаем номер сервера, текстовую строку аудио и хэш
try {
    $vk = new VK\VK($api_id, $api_key, $accessToken);
	
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, array('file' => '@'. $file));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data; charset=UTF-8'));
$json = json_decode(curl_exec($ch));
curl_close($ch);
$server = $json->server;
$audio = $json->audio;
$hash = $json->hash;
sleep(1);
} catch (VK\VKException $error) {
    die($error->getMessage());
}
//Спим секунду
sleep(1);
//Сохраняем результат и получаем данные
$res = $vk->api('audio.save', array(
            'server'       => $server,
            'audio'    => $audio,
            'hash'     => $hash
        ));
$audio_id = $res['response']['aid'];
$owner_id = $res['response']['owner_id'];
$nosearch = $vk->api('audio.edit', array(
            'owner_id'       => $owner_id,
            'audio_id'    => $audio_id,
            'no_search'     => 1,
			'text'     => "Musical Decadence\n\nhttp://vk.com/musicaldecadence\nhttp://musicaldecadence.ru"
        ));
unlink($file);
?>