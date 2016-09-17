<?php

	if (!ob_get_level()) ob_start();
	if ( is_file('inc/conf/channels.php') ) include 'inc/conf/channels.php'; else die('Unable to load channels information!');
	if ( !is_array($channels) ) $channels = array();

	// Check if selected channel data exists
	foreach ( $channels as $key => $channel ) {	if ( $channel['name'] == $_GET['c'] ) break; }
	if ( !is_array($channels[$key]) ) { die('Selected channel does not exist. It may been removed or moved. Please try again later.'); }


	// Generate Playlist and send it as attachment.
	session_write_close();

	header("Content-Description: File Transfer");
	header("Content-Transfer-Encoding: binary");
	header("Pragma: public");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	
	// Replace some characters in title to get radioname out
	$radioName = str_ireplace(array(' player'), '', $settings['title']);

	switch($_GET['pl']) {

		case "wmp":

			header("Content-Type: video/x-ms-asf");
			header("Content-Disposition: attachment; filename=\"Listen.asx\"");

			echo "<asx version=\"3.0\">\r\n";

			foreach ($channels[$key]['streams'] as $name => $link) {
				echo "<title>{$radioName}</title>\r\n<entry>\r\n<title>{$name}</title>\r\n<ref href=\"" . preg_replace("/;.*$/", '', $link['mp3']) . "\"/>\r\n</entry>\r\n";
			}

			echo '</asx>';

			break;

		case "quicktime":

			header("Content-Type: application/x-mpegurl");
			header("Content-Disposition: attachment; filename=\"Listen.m3u\"");

			$i=0;
			echo "#EXTM3U\r\n";
			foreach ($channels[$key]['streams'] as $name => $link) {
				echo "#EXTINF:{$i},{$name}\r\n" . preg_replace("/;.*$/", '', $link['mp3']) . "\r\n";
				$i++;
				echo (( !empty($link['oga']) ) ? "#EXTINF:{$i},{$name}\r\n" . preg_replace("/;.*$/", '', $link['oga']) . "\r\n" : '');
				$i++;
			}

			break;

		default:
			header("Content-Type: audio/x-scpls");
			header("Content-Disposition: attachment; filename=\"Listen.pls\"");

			$i=0;
			echo "[playlist]\r\n";
			foreach ($channels[$key]['streams'] as $name => $link) {
				$i++;
				echo "File{$i}=" . preg_replace("/;.*$/", '', $link['mp3']) . "\r\nTitle{$i}={$radioName} ({$name})\r\nLength{$i}=0\r\n\r\n";
			}

			echo "NumberOfEntries={$i}\r\n\r\nVersion=2";

			break;
	}

	ob_end_flush(); exit();

?>