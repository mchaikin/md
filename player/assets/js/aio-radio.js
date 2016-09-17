/* Global Script Variables (important!)
================================================================================================================== */
var tmp = {}, c = new Array();


/* This is executed when all scripts are loaded. This will also execute few other functions
================================================================================================================== */
$(function() {

	// Check if config is there!
	if ( typeof (s) == 'undefined' || s.setup == 'true' ) {
		$('.preloader .text_area').html('<span style="color: red;"><div style="font-weight: 500;">ERROR OCCURED</div>Unable to read player configuration!</span>');
		return false;
	}


	// Initial Load
	$.getJSON('?c=all', loadSettings).fail( function( jqxhr, textStatus, error ) { 

		console.log( "Request Failed: " + textStatus + ', ' + error);
		$('.preloader .text_area').html('<span style="color: red;"><div style="font-weight: 500;">ERROR OCCURED</div>Unable to load player configuration!</span>');
		return false; // Just in case

	});


	// Bind Facebook & Twitter buttons
	$('.share-area .facebook, .share-area .twitter, .share-area .vk').on('click', function() {

		if ( tmp['facebook-url'] == null || tmp['twitter-url'] == null || tmp['vk-url'] == null ) return false;
		window.open (( ($(this).hasClass('facebook')) ? tmp['facebook-url'] :
						( $(this).hasClass('vk') ? tmp['vk-url'] :
						tmp['twitter-url']  )), 'share', 'width=800, height=400');

		return false;

	});


	// Bind Header dropdowns
	$('.header li a').on('click', function() {

		var elm = $(this);

		if ( $(elm).hasClass('active') ) {

			$(elm).removeClass('active').next('ul').removeClass('active');

		} else {

			// Close all before opening new
			$('.header > ul > li > a').removeClass('active');
			$('.header > ul > li > ul').removeClass('active');

			// Open
			$(elm).addClass('active').next('ul').addClass('active');
			$(document).on('click', function() { $(elm).removeClass('active').next('ul').removeClass('active'); $('.stats, .player').unbind('click'); });

		}

		return false;

	});


});


/* Executed at load, this will get all channels and its settings
================================================================================================================== */
function loadSettings( data ) {

	// Javascript can cause issues if object is passed, so create javascript array from obj
	$.each(data, function(key, val) {
		c.push(val);	
	});


	// Check if channel's list exists, if not show message
	if ( c.length <= 0 ) {
		$('.preloader .text_area').html('<span style="color: red;"><div style="font-weight: 500;">NO CHANNELS DEFINED</div>Unable to find channels, please create one!</span>');
		return false;
	}


	// Generate list of channels
	if ( c.length > 1) {

		$('.channels').show();

		$.each(c, function(key, val) {

			tmp['html'] = $('<li><a tabindex="1" href="#' + val.name + '">' + val.name + '</a></li>');
			$('.channel-list').append(tmp['html']);

			// Bind channel change
			tmp['html'].on('click', function() {
				loadChannel($(this).text());
			});

		});
	}


	// Use hash to select a channel (method to post links to channels)
	var win_hash = window.location.hash.replace('#', '');
	if (win_hash != '') { loadChannel(win_hash); }

	// Use cookie for latest selected channel
	if ( win_hash == '' && s.usecookies == 'true' ) {

		var cok = getcookie('lastchannel');
		if ( cok != null ) {
			loadChannel(cok, true);
		}

	}

	// If default channel is defined, instead of first channel use the one from settings
	if ( win_hash == '' && cok == null && s.default_channel != null ) {
		loadChannel(s.default_channel, true);
	} 

	// Now if we still don't have channel, select default.
	if ( s.channel.name == null ) {
		loadChannel(c[0].name);
	}


	// All done, hide preloader
	$('.preloader').addClass('loadComplete');

};


/* Handle channel change (rebind things, change etc...)
================================================================================================================== */
function loadChannel(name, grace) {

	if ( s.channel.name == name ) return false;


	// Check if the channel exist
	for ( i = 0; i < c.length; i++ ) {
		if (c[i].name == name)
			var key_ok = i;
	}


	// Do the check with graceful fix for people with existing cookie
	if ( typeof(key_ok) != 'number' ) {

		if ( grace !== true ) {	alert ('Invalid Channel!');	} 
		console.log('Invalid channel: ' + name);
		return false;

	}


	// Handle list
	$('.channel-list li > a').removeClass('active');
	$('.channel-list li').find('a[href="#' + name + '"]').addClass('active');

	// Set active channel (for easier usage)
	s.channel = c[key_ok];
	setcookie('lastchannel', name, 365);


	// Load skin & logo
	$('#maintheme').attr('href', 'assets/css/' + s.channel['skin']);
	if ( s.channel.logo != null && s.channel.logo != '' ) { 

		var logoimg = new Image();
		logoimg.src = s.channel.logo;
		logoimg.onload = function() { // We will only change channel logo if it was loaded!
			$('.header .logo a img').attr('src', s.channel.logo);
		};

	} else {

		$('.header .logo a img').attr('src', 'assets/img/logo.png');

	}

	// Replace channel name in playlist files
	$('.playlists a').each(function() {
		$(this).attr('href', $(this).attr('href').replace(/c=(.*)/, 'c=' + name));
	});

	// Reset per channel stuff
	tmp.onair = null;

	// Show loading bellow artist image
	$('.artist-preload').show();
	$('.onair .time').html('00:00');


	// Check user settings for quality
	if ( s.usecookies == 'true' ) {

		var qualitycookie = getcookie('quality');
		if ( qualitycookie != null ) tmp.quality = qualitycookie;

	} else { tmp.quality = null; }


	// Set Quality Group (if no user defined
	if ( tmp.quality == null || tmp.quality == '' || s.channel.streams[tmp.quality] == null )
		for (tmp.quality in s.channel.streams) break;


	// Generate list of streams
	if ( $.map( s.channel.streams, function(n, i) { return i; } ).length > 1) {	// If more then one stream

		$('.settings').show();
		$('.streams-list').empty();

		$.each( s.channel.streams, function(key, val) {

			tmp['html'] = $('<li><a tabindex="1" href="#">' + key + '</a></li>');
			$('.streams-list').append(tmp['html']);

			// Add default active state
			if ( tmp.quality == key ) tmp['html'].find('a').addClass('active');


			// Bind channel change
			tmp['html'].on('click', function() {

				$('.streams-list li > a').removeClass('active');
				$(this).find('a').addClass('active');

				tmp.quality = $(this).text();
				setcookie('quality', tmp.quality, 365);
				initPlayer(); // Reload player

				return false;
			});

		});

	} else {

		$('.settings').hide();

	}


	// Now the heavy work: init player, show loading and start reading stats, again
	clearInterval(tmp.radioinfo);
	tmp.radioinfo = setInterval(radioInfo, (parseInt(s.stats_refresh) * 1000));
	radioInfo();

	initPlayer();
	txt(s.lang['status-stopped'], true);

};


/* Most important function of them all, use jPlayer lib to deploy player and set things up!
================================================================================================================== */
function initPlayer () {

	var solution, supplied, autoplay, supplied = '';


	// Use HTML5 Solution as primary and Flash as fallback if no solution is found (DEFAULT)
	solution = 'html, flash';


	// Check for solutions from stream urls
	var suparr = new Array();
	$.each( s.channel.streams[tmp.quality], function(key, value) { suparr.push(key); } );
	supplied = suparr.join(', ');

	// Auto play check
	if (s.autoplay == 'true') {
		autoplay = 'play';
	}


	// Check cookie for volume
	var gVol = getcookie('volume');
	gVol = ((gVol != null) ? gVol : '0.5')


	// Error, exit before attempting to deploy
	if ( s.channel.streams[tmp.quality] == null ) {
		alert('ERROR: There has been issue with player configuration !');
		return false;
	}


	// If we have active radio clear media to set new Quality
	$("#jplayer-object").jPlayer("destroy");

	// Initiate settings and object
	var obj = $("#jplayer-object"), ready = false;
	obj.jPlayer({

		swfPath: "assets/flash/jquery.jplayer.swf",
		solution: solution,
		supplied: supplied,
		smoothPlayBar: false,
		errorAlerts: false,
		cssSelectorAncestor: "",
		volume: gVol,
		preload: 'none',
		cssSelector: {
			play: ".play",
			pause: ".stop",
			mute: ".volume-icon #volume",
			unmute: ".volume-icon #muted",
			volumeBar: ".volume-slider .vol-progress",
			volumeBarValue: ".volume-slider .vol-progress .vol-bar",
		},

		ready: function (event) {

			if(event.jPlayer.status.noVolume) {

				// Add a class and then CSS rules deal with it.
				$('.volume-control').addClass('no-volume');
				$('.volume-slider .player-status').css({ 'margin-top': '0' });

			}

			ready = true;

			if ( s.channel.streams[tmp.quality] == null ) {
				alert('ERROR: There has been issue with player configuration !');
				return false;
			}

			$(this).jPlayer('setMedia', s.channel.streams[tmp.quality]).jPlayer(autoplay);

		},

		pause: function() {
			$(this).jPlayer('clearMedia');
			txt(s.lang['status-stopped'], true);
		},

		error: function(event) {

			if (ready && event.jPlayer.error.type === $.jPlayer.error.URL_NOT_SET) {

				// Setup the media stream again and play it.
				$(this).jPlayer("setMedia", s.channel.streams[tmp.quality]).jPlayer('play');

			} else if (ready && event.jPlayer.error.type === $.jPlayer.error.URL) { 

				txt('Ошибка: Невозможно подключиться к потоку!', true);

			} else {

				$('.preloader').removeClass('loadComplete').css({ 'visibility': 'visible', 'opacity' : 1 });
				$('.preloader .text_area').html('<span style="color: red;"><div style="font-weight: 500;">PLAYBACK ERROR</div> ' + event.jPlayer.error.message + '</span>');

			}
		},

		volumechange: function(event) {

			// Change main volume icons
			if (event.jPlayer.options.muted) {

				$('.volume-icon #volume').hide();
				$('.volume-icon #muted').show();

			} else {

				$('.volume-icon #muted').hide();
				$('.volume-icon #volume').show();

			}

			if (event.jPlayer.options.muted) { txt(s.lang['status-muted']); } else { txt(s.lang['status-volume'].replace('{LEVEL}', Math.floor(event.jPlayer.options.volume * 100) + '%')); }
			setcookie('volume', parseFloat(event.jPlayer.options.volume).toFixed(2), 365);

		}

	});


	// Create the volume slider control
	$('.volume-control').mousedown(function() {

		// Select specific element
		parent = $('.volume-slider .vol-progress');

		// Disable selecting any text on body while moving mouse
		$('body').css({ 
			'-ms-user-select': 'none',
			'-moz-user-select': 'none',
			'-webkit-user-select': 'none',
			'user-select': 'none'
		});

		// Bind mouse move event
		$(document).mousemove(function(e) {

			// Only work within the left/right limit
			if ( (e.pageX - $(parent).offset().left) < 1 ) { return false; }

			// Set other settings/variables
			var total = $('.volume-slider .vol-progress').width();
			obj.jPlayer("option", "muted", false);
			obj.jPlayer("option", "volume", (e.pageX - $(parent).offset().left +1) / total);
			tmp.moving = true;

		});

		// Unbind mousemove once we release mouse
		$(document).mouseup(function() {

			// Allow selecting text after releasing drag & drop
			$('body').removeAttr('style');

			// Unbind move events
			$(document).unbind('mousemove');

		});

	});


	// If Playlist is clicked, stop playback
	$('.playlists a').unbind('click').on('click', function() {

		if ( ready == true ) {
			obj.jPlayer('clearMedia'); 
			txt(s.lang['status-stopped'], true);
		}

	});


	// Remove loading message when player starts playing
	obj.unbind($.jPlayer.event.play); obj.unbind($.jPlayer.event.playing);
	obj.bind($.jPlayer.event.play, function(event) { txt(s.lang['status-init'].replace('{STREAM}', s.channel.name), true); });
	obj.bind($.jPlayer.event.playing, function(event) { txt(s.lang['status-playing'].replace('{STREAM}', s.channel.name), true); });

};


/* Few helper functions, not so much important that's why they're on bottom :)
================================================================================================================== */
function radioInfo() { // Ajax calls to get stream information

	// No channel yet? fine...
	if ( s.channel.name == null ) { return false; }


	// Call ajax
	$.ajax({

		url: 'index.php?c=' + s.channel.name,
		cache: false,
		dataType: 'json',
		timeout: (parseInt(s.stats_refresh) * 1000) - 1000, // Stats Refresh Speed -1000ms

		success: function(data) {

			// Few checks to ensure empty data isn't displayed and that we use tmp storage for artist/title
			if ( tmp.onair == null ) tmp.onair = {};			
			if ( data.artist == null || data.title == null ) return false;
			if ( data.artist == tmp.onair.artist && data.title == tmp.onair.title ) return false;

			// Now we're done with checks, do DOM content changes etc...
			$('.stats .artist').html('<a class="css-hint" data-title="' + data.artist + '" href="#">' + shorten(data.artist, s.artist_length) + '</a>'); 		// Change artist
			$('.stats .title').html('<a class="css-hint" data-title="' + data.title + '" href="#">' + shorten(data.title, s.title_length) + '</a>');			// Change title

			// Load image with preloader
			$('.artist-preload').show();
			$('#artist-img').attr('src', data.image).one('load', function() {
				$('.artist-preload').hide();
			});

			// If TRUE change window title on track load
			if ( s.dynamic_title == 'true' ) {
				if ( tmp.ptitle == null ) tmp['ptitle'] = document.title;
				document.title = data.artist + ' - ' + data.title + ' | ' + tmp['ptitle'];
			}

			// Check what do we share with twitter, radio name + channel or artist/title
			if ( data.artist == s.default_artist && data.title == s.default_title ) {

				var twitter_title 	= '' + s.title + ' #' + s.channel.name + '';

			} else { // Use artist & title

				var twitter_title 	= '"' + data.artist + ' - ' + data.title + '"';

			}

			// Global variables for Twitter & Facebook (Share/Tweet URL's)
			var currentURL = 'http://musicaldecadence.ru';
			tmp['facebook-url'] 	= 'https://www.facebook.com/sharer/sharer.php?u=' + currentURL; /* ?fbc=' + s.channel.name + '&a=' + data.artist + '&t=' + data.title; */
			tmp['twitter-url'] 		= 'https://twitter.com/share?url=' + currentURL + '&text=' + encodeURIComponent(s.lang['twitter-share'].replace('{TRACK}', twitter_title));
			tmp['vk-url'] 		= 'https://vk.com/share.php?url=' + currentURL + '&title=Musical Decadence' + '&description=' + encodeURIComponent(s.lang['twitter-share'].replace('{TRACK}', twitter_title)) + '&image=http://musicaldecadence.ru/test/img/logo.png';

			// Set TMP variables
			tmp.onair = data;
			tmp.onair.timer = new Date().getTime();
			onairTime();

			// Disable checking if stats are disabled (this is a fix)
			if ( data.status != null && data.status == 'disabled' ) {
				clearInterval(tmp.radioinfo); 	// Stop refreshing
			}


	}}).fail(function( jqxhr, textStatus, error ) { $('.artist-preload').hide(); console.log( "Request Failed: " + textStatus + ', ' + error); });

}

// Simple function to show time since track was changed
function onairTime() {

	clearInterval(tmp.timer);

	// Don't attempt anything else if disabled
	if ( s.channel['show-time'] != true ) { $('.onair .time').html('00:00').hide(); return false; }

	// Clear and Reset interval
	tmp.timer = setInterval(function() {	

		// Exit if setting disabled
		if ( s.channel['show-time'] != true ) { clearInterval(tmp.timer); $('.onair .time').hide(); return false; }

		// Exit if "start" time is empty
		if ( tmp.onair == null || typeof (tmp.onair.timer) != 'number' ) return false;

		// Set var for easier management
		var ctime = ((new Date().getTime()) - tmp.onair.timer) / 1000;

		// Devide etc to show time with format
		var hour = ( ( Math.floor( ( ctime / 3600 ) % 60 ) > 10 ) ? Math.floor( ( ctime / 3600 ) % 60 ) : '0' + Math.floor( ( ctime / 3600 ) % 60 ) );
		var min  = ( ( Math.floor( ( ctime / 60 ) % 60 ) > 10 ) ? Math.floor( ( ctime / 60) % 60 ) : '0' + Math.floor( ( ctime / 60 ) % 60 ) );
		var sec  = ( ( ( ctime % 60 ) > 10 ) ? Math.floor( ctime % 60 ) : '0' + Math.floor( ctime % 60 ) );

		// Display only active timer (1h 2min 3sec)
		if (hour >= 1) { timer = hour + ':' + min + ':' + sec + ''; }
		else { timer = min + ':' + sec + ''; }

		// Write playtime on player
		$('.onair .time').show().html(timer);

		}, 1000);

}

// Simple function to use some jquery magic
function txt(text, perment) {

	var status = $('.player-status');

	// Set previous text into data-name attribute
	if ( perment == true || typeof(tmp['txt-status']) == undefined ) {
		tmp['txt-status'] = text;
	}

	// Don't set new timeout of there is one already!
	if (tmp['txtobj'] != null) {
		clearTimeout(tmp['txtobj']);
	}

	// Set new text into the element
	status.html(text);

	// Create Timer into window.object
	if ( perment == null ) {
		tmp['txtobj'] = setTimeout(function() { status.hide().html(tmp['txt-status']).fadeIn('slow'); }, 2000);
	}

}

// Shorten a string by specified length
function shorten($text, $length) {

	// Skip if max length defined zero
	if ( $length == '0' ) return $text;

	// Do the magic
	var length = $length || 10;
	if ($text.length > length) {
		$text = $text.substring(0, length)+'&hellip;';
	}

	return $text;
}

// Simple function to write cookie
function setcookie(name, value, expires, path, domain, secure) {

	if ( s.usecookies != 'true' ) { return null; } // Cookies not enabled!
	var today = new Date();
	today.setTime(today.getTime());

	if (expires) {
		expires = expires * 1000 * 60 * 60 * 24;
	}

	var expires_date = new Date(today.getTime() + (expires));

	document.cookie = name+'=' + escape(value) +
	((expires) ? ';expires='+ expires_date.toGMTString() : '') + //expires.toGMTString()
	((path) ? ';path=' + path : '') +
	((domain) ? ';domain=' + domain : '') +
	((secure) ? ';secure' : '');

}

// Read cookievalue
function getcookie( name ) {

	if ( s.usecookies != 'true' ) { return null; } // Cookies not enabled!
	var start = document.cookie.indexOf(name + "=");
	var len = start + name.length + 1;

	if ((!start) && (name != document.cookie.substring(0, name.length))) {
		return null;
	}

	if (start == -1) return null;

	var end = document.cookie.indexOf(';', len);

	if (end == -1) end = document.cookie.length;
	return unescape( document.cookie.substring(len, end));

}

// Delete cookie
function delcookie( name, path, domain ) {

	if ( s.usecookies != 'true' ) { return null; } // Cookies not enabled!	
	if (getcookie( name )) document.cookie = name + '=' +
		((path) ? ';path=' + path : '') +
		((domain) ? ';domain=' + domain : '') +
		';expires=Thu, 01-Jan-1970 00:00:01 GMT';

}

function isTouchDevice(){
	return typeof window.ontouchstart !== 'undefined';
}