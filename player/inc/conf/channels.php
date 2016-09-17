<?php $channels = array (
  0 => 
  array (
    'name' => 'Musical Decadence Radio',
    'logo' => 'tmp/images/logo.1458053502.jpg',
    'skin' => 'MD_Theme.css',
    'show-time' => false,
    'streams' => 
    array (
      'Default Quality' => 
      array (
        'mp3' => 'http://www.musicaldecadence.ru:8000/live',
      ),
    ),
    'stats' => 
    array (
      'method' => 'icecast',
      'url' => 'http://192.168.1.40:8000',
      'auth-user' => 'admin',
      'auth-pass' => 'karuba',
      'mount' => '/live',
      'fallback' => '',
    ),
  ),
  1 => 
  array (
    'name' => 'Musical Decadence Radio',
    'logo' => 'tmp/images/logo.1461858569.jpg',
    'skin' => 'html5-radio.css',
    'show-time' => false,
    'streams' => 
    array (
      'Default Quality' => 
      array (
        'mp3' => 'http://musicaldecadence.ru:8000/live',
      ),
    ),
    'stats' => 
    array (
      'method' => 'icecast',
      'url' => 'http://musicaldecadence.ru:8000',
      'auth-user' => 'admin',
      'auth-pass' => 'karuba',
      'mount' => '/live',
      'fallback' => '',
    ),
  ),
); ?>