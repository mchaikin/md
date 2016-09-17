/*
 * HTML5 Audio Player PRO - v1.8.2
 *
 * Copyright 2014, LambertGroup
 * 
 */

(function($) {
	
	//vars	
	var val = navigator.userAgent.toLowerCase();
		
	//functions		
	function supports_mp3_audio(current_obj) {
			  var a = document.getElementById(current_obj.audioID);
			  return !!(a.canPlayType && a.canPlayType('audio/mpeg;').replace(/no/, ''));
	}	
	
	function detectBrowserAndAudio(current_obj,options,audio2_html5_thumbsHolder,audio2_html5_container) {
				//activate current
				//$(current_obj.thumbsHolder_Thumbs[current_obj.current_img_no]).addClass('thumbsHolder_ThumbON');
				$(current_obj.thumbsHolder_Thumbs[current_obj.current_img_no]).css({
					"background":options.playlistRecordBgOnColor,
					"border-bottom-color":options.playlistRecordBottomBorderOnColor,
					"color":options.playlistRecordTextOnColor
				});
				
				//auto scroll carousel if needed
				if (!current_obj.is_very_first) {
					carouselScroll(-1,current_obj,options,audio2_html5_thumbsHolder);
				}
				//alert ("detect: "+current_obj.origID+'  ---  '+current_obj.playlist_arr[current_obj.origID]['sources_mp3']);
				var currentAudio=current_obj.playlist_arr[current_obj.origID]['sources_mp3'];
				
				//alert (val);
				if (val.indexOf("opera") != -1 || val.indexOf("firefox") != -1  || val.indexOf("mozzila") != -1) {
					currentAudio=current_obj.playlist_arr[current_obj.origID]['sources_ogg'];
					if (supports_mp3_audio(current_obj)!='') {
						currentAudio=current_obj.playlist_arr[current_obj.origID]['sources_mp3'];
					}	
				}
					
				if (val.indexOf("chrome") != -1 || val.indexOf("msie") != -1 || val.indexOf("safari") != -1) {
					currentAudio=current_obj.playlist_arr[current_obj.origID]['sources_mp3'];
					if (val.indexOf("opr") != -1) {
						currentAudio=current_obj.playlist_arr[current_obj.current_img_no]['sources_ogg'];
						if (supports_mp3_audio(current_obj)!='') {
							currentAudio=current_obj.playlist_arr[current_obj.origID]['sources_mp3'];
						}	
					}			
				}
					
				if (val.indexOf("android") != -1)
					currentAudio=current_obj.playlist_arr[current_obj.origID]['sources_mp3'];				
				
				//if (val.match(/(iPad)|(iPhone)|(iPod)|(webOS)/i))
				if (val.indexOf("ipad") != -1 || val.indexOf("iphone") != -1 || val.indexOf("ipod") != -1 || val.indexOf("webos") != -1)
					currentAudio=current_obj.playlist_arr[current_obj.origID]['sources_mp3'];

				//alert (currentAudio+ '  --  ' +val);
				return currentAudio;
			};			
	
					
	
	function changeSrc(current_obj,options,audio2_html5_thumbsHolder,audio2_html5_container,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_Author,audio2_html5_Audio,audio2_html5_ximage) {

				current_obj.totalTime = 'Infinity';
				//seekbar init
				if (options.isSliderInitialized) {
					audio2_html5_Audio_seek.slider("destroy");
					options.isSliderInitialized=false;
				}
				if (options.isProgressInitialized) {
					audio2_html5_Audio_buffer.progressbar("destroy");
					options.isProgressInitialized=false;
				}
				//audio2_html5_Audio.unbind('progress');
				current_obj.is_changeSrc=true;
				current_obj.is_buffer_complete=false;
				
				//current_obj.totalTimeInterval='Infinity';
				
				//audio2_html5_Title init
			/*audio2_html5_ximage.css({
				'left':current_obj.timerLeftPos+'px',
				'top':current_obj.timerTopPos+audio2_html5_Audio_timer_a.height()+current_obj.constantDistance+'px'
			});*/
				audio2_html5_Title.width(current_obj.titleWidth);
				audio2_html5_Author.width(current_obj.titleWidth);
				audio2_html5_Audio_buffer.css({'background':options.bufferEmptyColor});
				
				//.each(function(){ alert ("aaaa"); });
				
				//current_obj.origID=;
				
				current_obj.curSongText='';
				/*if (options.showAuthor && current_obj.playlist_arr[current_obj.origID]['author']!=null && current_obj.playlist_arr[current_obj.origID]['author']!='') {
	            	current_obj.curSongText+=current_obj.playlist_arr[current_obj.origID]['author']+' - ';
				}*/		       
				if (options.showTitle && current_obj.playlist_arr[current_obj.origID]['title']!=null && current_obj.playlist_arr[current_obj.origID]['title']!='') {
	            	current_obj.curSongText+=current_obj.playlist_arr[current_obj.origID]['title'];
	            }
				current_obj.isAuthorTitleInsideScrolling=false;
				current_obj.authorTitleInsideWait=0;
				audio2_html5_TitleInside.stop();
				audio2_html5_TitleInside.css({'margin-left':0});	
				audio2_html5_TitleInside.html(current_obj.curSongText);
				
				if (options.showAuthor && current_obj.playlist_arr[current_obj.origID]['author']!=null && current_obj.playlist_arr[current_obj.origID]['author']!='') {
					audio2_html5_Author.html(current_obj.playlist_arr[current_obj.origID]['author']);
				}
				
				audio2_html5_ximage.html('<img src="'+current_obj.playlist_arr[current_obj.origID]['image']+'" width="80">');
					
				if (!current_obj.curSongText) {
					audio2_html5_Title.css({
						'display':'none',
						'width':0,
						'height':0,
						'padding':0,
						'margin':0
					});
				}
					
				
				//audio2_html5_Audio.type='audio/ogg; codecs="vorbis"';
				var the_file=detectBrowserAndAudio(current_obj,options,audio2_html5_thumbsHolder,audio2_html5_container);
				document.getElementById(current_obj.audioID).src=the_file;
				document.getElementById(current_obj.audioID).load();
				
				if (options.googleTrakingOn) {
					ga('send', 'event', 'Audio Files', 'Play', 'Title: '+current_obj.playlist_arr[current_obj.origID]['title']+'  ---  File: '+the_file);
				}				
				
				//alert (audio2_html5_Audio.type );
				
				
				if (val.indexOf("android") != -1) {
					//nothing
				} else if ((val.indexOf("ipad") != -1 || val.indexOf("iphone") != -1 || val.indexOf("ipod") != -1 || val.indexOf("webos") != -1) && current_obj.is_very_first) {
					//nothing
				} else {
					if (options.autoPlay) {
						cancelAll();
						document.getElementById(current_obj.audioID).play();
						//audio2_html5_play_btn.click();
						audio2_html5_play_btn.addClass('AudioPause');
					} else {
						audio2_html5_play_btn.removeClass('AudioPause');
					}
				}

			};
			
			


			function FormatTime(seconds){
				var m=Math.floor(seconds/60)<10?"0"+Math.floor(seconds/60):Math.floor(seconds/60);
				var s=Math.floor(seconds-(m*60))<10?"0"+Math.floor(seconds-(m*60)):Math.floor(seconds-(m*60));
				return m+":"+s;
			};

        



			function generate_seekBar(current_obj,options,audio2_html5_container,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_play_btn,audio2_html5_Audio) {
				//alert ("gen: "+document.getElementById(current_obj.audioID).readyState);
					current_obj.is_changeSrc=false;
					if (current_obj.is_very_first)
						current_obj.is_very_first=false;
					//initialize the seebar
					//alert (audio2_html5_Audio_timer_b.width());
					audio2_html5_Audio_buffer.width(options.playerWidth-2*current_obj.timerLeftPos-2*audio2_html5_Audio_timer_a.width()-2*current_obj.seekBarLeftRightSpacing); //15 the buffer 


					current_obj.bufferTopPos=current_obj.timerTopPos+parseInt((audio2_html5_Audio_timer_a.height()-audio2_html5_Audio_buffer.height())/2,10);	
					current_obj.bufferLeftPos=parseInt((audio2_html5_container.width()-audio2_html5_Audio_buffer.width())/2);				
					audio2_html5_Audio_buffer.css({
						'top':current_obj.bufferTopPos+'px',
						'left':current_obj.bufferLeftPos+'px'
						//'left':parseInt(current_obj.timerLeftPos+audio2_html5_Audio_timer_a.width()+current_obj.seekBarLeftRightSpacing)+'px'
					});
					audio2_html5_Audio_seek.width(audio2_html5_Audio_buffer.width());
					audio2_html5_Audio_seek.css({
						'top':current_obj.bufferTopPos+'px',
						'left':current_obj.bufferLeftPos+'px'
						//'left':parseInt(current_obj.timerLeftPos+audio2_html5_Audio_timer_a.width()+current_obj.seekBarLeftRightSpacing)+'px'
					});
					
					audio2_html5_Audio_seek.slider({
						value: 0,
						step: 0.01,
						orientation: "horizontal",
						range: "min",
						max: current_obj.totalTime,
						//animate: true,					
						slide: function(){							
							current_obj.is_seeking = true;
						},
						stop:function(e,ui){
							current_obj.is_seeking = false;						
							document.getElementById(current_obj.audioID).currentTime=ui.value;
							if(document.getElementById(current_obj.audioID).paused != false) {
								document.getElementById(current_obj.audioID).play();
								audio2_html5_play_btn.addClass('AudioPause');				
							}
							
						},
						create: function( e, ui ) {
							options.isSliderInitialized=true;
						}
					});
					$(".ui-slider-range",audio2_html5_Audio_seek).css({'background':options.seekbarColor});
					
					
					
					var bufferedTime=0;
					audio2_html5_Audio_buffer.progressbar({ 
						value: bufferedTime,
						complete: function(){							
							current_obj.is_buffer_complete=true;
						},
						create: function( e, ui ) {
							options.isProgressInitialized=true;
						}
					});
					$(".ui-widget-header",audio2_html5_Audio_buffer).css({'background':options.bufferFullColor});
			

				
			};

			
		
			
			function seekUpdate(current_obj,options,audio2_html5_container,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_play_btn,audio2_html5_Audio,audio2_html5_Title,audio2_html5_TitleInside) {
				if (!current_obj.isAuthorTitleInsideScrolling && current_obj.authorTitleInsideWait>=5 && audio2_html5_TitleInside.width()>current_obj.titleWidth) {
					current_obj.isAuthorTitleInsideScrolling=true;
					current_obj.authorTitleInsideWait=0;
					audio2_html5_TitleInside.html(current_obj.curSongText+" **** "+current_obj.curSongText+" **** "+current_obj.curSongText+" **** "+current_obj.curSongText+" **** "+current_obj.curSongText+" **** ");
					audio2_html5_TitleInside.css({'margin-left':0});					
					audio2_html5_TitleInside.stop().animate({
							'margin-left':(options.playerWidth-audio2_html5_TitleInside.width())+'px'
					 }, parseInt((audio2_html5_TitleInside.width()-options.playerWidth)*10000/150,10), 'linear', function() {
							// Animation complete.
							  current_obj.isAuthorTitleInsideScrolling=false;
					});
				} else if (!current_obj.isAuthorTitleInsideScrolling && audio2_html5_TitleInside.width()>current_obj.titleWidth) {
					current_obj.authorTitleInsideWait++;
				}
				
				//update time
				curTime = document.getElementById(current_obj.audioID).currentTime;
				bufferedTime=0;
				if (current_obj.is_changeSrc && !isNaN(current_obj.totalTime) && current_obj.totalTime!='Infinity') {
					//alert (current_obj.totalTime);
					generate_seekBar(current_obj,options,audio2_html5_container,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_play_btn,audio2_html5_Audio);
					if (val.indexOf("android") != -1) {
						if (options.autoPlay) {
							document.getElementById(current_obj.audioID).play();
							//audio2_html5_play_btn.click();
							audio2_html5_play_btn.addClass('AudioPause');
						} else {
							audio2_html5_play_btn.removeClass('AudioPause');
						}
					}
				}
					
						
						//update seekbar
						if(!current_obj.is_seeking && options.isSliderInitialized)
							audio2_html5_Audio_seek.slider('value', curTime);
						
						//the buffer	
						if (val.indexOf("android") != -1) {
							//fix duration android 4 start
							if (current_obj.totalTime!=document.getElementById(current_obj.audioID).duration && document.getElementById(current_obj.audioID).duration>0) {
								current_obj.totalTime=document.getElementById(current_obj.audioID).duration;
								//seekbar init
								if (options.isSliderInitialized) {
									audio2_html5_Audio_seek.slider("destroy");
									options.isSliderInitialized=false;
								}
								if (options.isProgressInitialized) {
									audio2_html5_Audio_buffer.progressbar("destroy");
									options.isProgressInitialized=false;
								}								
								generate_seekBar(current_obj,options,audio2_html5_container,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_play_btn,audio2_html5_Audio);
							}
							//fix duration android 4 start
							
							audio2_html5_Audio_buffer.css({'background':options.bufferFullColor});
							if (!isNaN(current_obj.totalTime) && current_obj.totalTime!='Infinity') {
								audio2_html5_Audio_timer_a.text(FormatTime(curTime));
								audio2_html5_Audio_timer_b.text(FormatTime(current_obj.totalTime));
							} else {
								audio2_html5_Audio_timer_a.text('00:00');
								audio2_html5_Audio_timer_b.text(FormatTime(0));
							}
						} else {
								if (document.getElementById(current_obj.audioID).buffered.length) {
									bufferedTime = document.getElementById(current_obj.audioID).buffered.end(document.getElementById(current_obj.audioID).buffered.length-1); 
									//alert (current_obj.totalTime + ' > '+bufferedTime);
									if (bufferedTime>0 && !current_obj.is_buffer_complete && !isNaN(current_obj.totalTime) && current_obj.totalTime!='Infinity' && options.isProgressInitialized) {
										audio2_html5_Audio_buffer.progressbar({ value: bufferedTime*100/current_obj.totalTime });
										//alert (bufferedTime+' -- '+options.playerWidth);
									}
								}
								audio2_html5_Audio_timer_a.text(FormatTime(curTime));
								audio2_html5_Audio_timer_b.text(FormatTime(bufferedTime));
						} 
				/*} else {
					audio2_html5_Audio_timer.text('00:00 / '+FormatTime(0));
				}*/
				
					
					
			};
			
			
			function endAudioHandler(current_obj,options,audio2_html5_container,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_next_btn,audio2_html5_Audio) {
		        if (options.loop) {
					audio2_html5_next_btn.click();
		        }
		    }		
			
			
		//playlist scroll
		function carouselScroll(direction,current_obj,options,audio2_html5_thumbsHolder) {
				var MAX_TOP=(current_obj.thumbsHolder_ThumbHeight+1)*(current_obj.selectedCateg_total_images-options.numberOfThumbsPerScreen);
				//alert (current_obj.audio2_html5_sliderVertical.slider( "option", "animate" ));
				audio2_html5_thumbsHolder.stop(true,true);
				if (direction!=-1 && !current_obj.isCarouselScrolling) {
					current_obj.isCarouselScrolling=true;
					audio2_html5_thumbsHolder.animate({
					    //opacity: 1,
					    //top:parseInt(MAX_TOP*(direction-100)/100,10)+'px'
						top:((direction<=2)?(-1)*MAX_TOP:parseInt(MAX_TOP*(direction-100)/100,10))+'px'
					  }, 1100, 'easeOutQuad', function() {
					    // Animation complete.
						  current_obj.isCarouselScrolling=false;
					});
				} else if (!current_obj.isCarouselScrolling && current_obj.selectedCateg_total_images>options.numberOfThumbsPerScreen) {
					current_obj.isCarouselScrolling=true;
					//audio2_html5_thumbsHolder.css('opacity','0.5');			
					var new_top=(-1)*parseInt((current_obj.thumbsHolder_ThumbHeight+1)*current_obj.current_img_no,10);
					if( Math.abs(new_top) > MAX_TOP ){ new_top = (-1)*MAX_TOP; }		
					if (current_obj.selectedCateg_total_images>options.numberOfThumbsPerScreen && options.showPlaylist) {			
						current_obj.audio2_html5_sliderVertical.slider( "value" , 100 + parseInt( new_top * 100 / MAX_TOP ) );
					}
					audio2_html5_thumbsHolder.animate({
					    //opacity: 1,
					    top:new_top+'px'
					  }, 500, 'easeOutCubic', function() {
					    // Animation complete.
						  current_obj.isCarouselScrolling=false;
					});
				}
			};
			
			

		function generateCategories(current_obj,options,audio2_html5_container,audio2_html5_thumbsHolder,audio2_html5_thumbsHolderWrapper,audio2_html5_thumbsHolderVisibleWrapper,audio2_html5_selectedCategDiv,audio2_html5_innerSelectedCategDiv,audio2_html5_searchDiv,audio2_html5_playlistPadding,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_Author,audio2_html5_Audio,audio2_html5_ximage) {
			  audio2_html5_thumbsHolder.stop(true,true);
			  current_obj.isCarouselScrolling=false;
			  
			  audio2_html5_thumbsHolder.stop().animate({
				  'left': (-1)*audio2_html5_thumbsHolderVisibleWrapper.width()+'px'
			  }, 600, 'easeOutQuad', function() {
				  // Animation complete.
					audio2_html5_thumbsHolder.html("");
					
//current_obj.numberOfCategories=current_obj.category_arr.length;
					for (var j=0;j<current_obj.category_arr.length;j++) {
							current_obj.thumbsHolder_Thumb = $('<div class="thumbsHolder_ThumbOFF" rel="'+ j +'"><div class="padding">'+current_obj.category_arr[j]+'</div></div>');
							audio2_html5_thumbsHolder.append(current_obj.thumbsHolder_Thumb);
							
			
							current_obj.thumbsHolder_Thumb.css({
								"top":(current_obj.thumbsHolder_Thumb.height()+1)*j+'px',
								"background":options.categoryRecordBgOffColor,
								"border-bottom-color":options.categoryRecordBottomBorderOffColor,
								"color":options.categoryRecordTextOffColor
							});				
							
							//activate current
							if (current_obj.category_arr[j]==current_obj.selectedCateg) {
								current_obj.current_img_no=j;
								current_obj.thumbsHolder_Thumb.css({
									"background":options.categoryRecordBgOnColor,
									"border-bottom-color":options.categoryRecordBottomBorderOnColor,
									"color":options.categoryRecordTextOnColor
								});
							}
					}
						
					current_obj.selectedCateg_total_images=current_obj.numberOfCategories;	
					current_obj.categsAreListed=true;
						
					/*audio2_html5_thumbsHolderWrapper.height(2*options.playlistPadding+(current_obj.thumbsHolder_Thumb.height()+1)*((options.numberOfThumbsPerScreen<current_obj.numberOfCategories)?options.numberOfThumbsPerScreen:current_obj.numberOfCategories)+audio2_html5_selectedCategDiv.height()+audio2_html5_searchDiv.height()+2*options.selectedCategMarginBottom+4); //current_obj.thumbsHolder_Thumb.height()+1 - 1 is the border
					audio2_html5_thumbsHolderVisibleWrapper.height((current_obj.thumbsHolder_Thumb.height()+1)*((options.numberOfThumbsPerScreen<current_obj.numberOfCategories)?options.numberOfThumbsPerScreen:current_obj.numberOfCategories));	*/
					audio2_html5_thumbsHolderWrapper.height(2*options.playlistPadding+(current_obj.thumbsHolder_Thumb.height()+1)*options.numberOfThumbsPerScreen+audio2_html5_selectedCategDiv.height()+audio2_html5_searchDiv.height()+2*options.selectedCategMarginBottom); //current_obj.thumbsHolder_Thumb.height()+1 - 1 is the border
					audio2_html5_thumbsHolderVisibleWrapper.height((current_obj.thumbsHolder_Thumb.height()+1)*options.numberOfThumbsPerScreen);
					audio2_html5_playlistPadding.css({'padding':options.playlistPadding+'px'});
					
					current_obj.thumbsHolder_Thumbs=$('.thumbsHolder_ThumbOFF', audio2_html5_container);
					
					//the playlist scroller
					if (current_obj.numberOfCategories>options.numberOfThumbsPerScreen && options.showPlaylist) {
						if (options.isPlaylistSliderInitialized) {
							current_obj.audio2_html5_sliderVertical.slider( "destroy" );
						}
						current_obj.audio2_html5_sliderVertical.slider({
							orientation: "vertical",
							range: "min",
							min: 1,
							max: 100,
							step:1,
							value: 100,
							slide: function( event, ui ) {
								//alert( ui.value );
								carouselScroll(ui.value,current_obj,options,audio2_html5_thumbsHolder);
							}
						});
						 options.isPlaylistSliderInitialized=true;
						//var audio2_html5_selectedCategDiv = $('.selectedCategDiv', audio2_html5_container);
					    //var audio2_html5_searchDiv = $('.searchDiv', audio2_html5_container);
						current_obj.audio2_html5_sliderVertical.css({
							'display':'inline',
							'position':'absolute',
							'height':audio2_html5_thumbsHolderWrapper.height()-20-audio2_html5_selectedCategDiv.height()-2*options.selectedCategMarginBottom-audio2_html5_searchDiv.height()-2*options.playlistPadding+'px', // 24 is the height of  .slider-vertical.ui-slider .ui-slider-handle
							'left':audio2_html5_container.width()+2*options.playerPadding-current_obj.audio2_html5_sliderVertical.width()-options.playlistPadding+'px',
							'top':current_obj.audioPlayerHeight+options.playlistTopPos+options.playlistPadding+audio2_html5_selectedCategDiv.height()+options.selectedCategMarginBottom+2+'px'
						});
						
						if (!options.showPlaylistOnInit)
							current_obj.audio2_html5_sliderVertical.css({
								'opacity': 0,
								'display':'none'
							});
						options.showPlaylistOnInit=true; // to prevent sliderVertical disappereance after yo show the playlist
							
						$('.thumbsHolder_ThumbOFF', audio2_html5_container).css({
							'width':audio2_html5_container.width()+2*options.playerPadding-current_obj.audio2_html5_sliderVertical.width()-2*options.playlistPadding-3+'px'
						});						
		
					} else {
						if (options.isPlaylistSliderInitialized) {
							current_obj.audio2_html5_sliderVertical.slider( "destroy" );
							options.isPlaylistSliderInitialized=false;
						}
						$('.thumbsHolder_ThumbOFF', audio2_html5_container).css({
							'width':audio2_html5_container.width()+2*options.playerPadding-2*options.playlistPadding+'px'
						});					
					}						
					
					



					//tumbs nav
					
					current_obj.thumbsHolder_Thumbs.click(function() {
							var currentBut=$(this);
							var i=currentBut.attr('rel');
							current_obj.selectedCateg=current_obj.category_arr[i];
							audio2_html5_innerSelectedCategDiv.html(current_obj.selectedCateg);
							generatePlaylistByCateg(current_obj,options,audio2_html5_container,audio2_html5_thumbsHolder,audio2_html5_thumbsHolderWrapper,audio2_html5_thumbsHolderVisibleWrapper,audio2_html5_selectedCategDiv,audio2_html5_searchDiv,audio2_html5_playlistPadding,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_Author,audio2_html5_Audio,audio2_html5_ximage);

					});	
					
					
					current_obj.thumbsHolder_Thumbs.mouseover(function() {
						var currentBut=$(this);
						currentBut.css({
							"background":options.categoryRecordBgOnColor,
							"border-bottom-color":options.categoryRecordBottomBorderOnColor,
							"color":options.categoryRecordTextOnColor
						});				
					});
					
					
					current_obj.thumbsHolder_Thumbs.mouseout(function() {
						var currentBut=$(this);
						var i=currentBut.attr('rel');
						if (current_obj.current_img_no!=i){
							currentBut.css({
								"background":options.categoryRecordBgOffColor,
								"border-bottom-color":options.categoryRecordBottomBorderOffColor,
								"color":options.categoryRecordTextOffColor
							});
						}
					});		

				//carouselScroll(-1,current_obj,options,audio2_html5_thumbsHolder);
				// mouse wheel
				audio2_html5_thumbsHolderVisibleWrapper.mousewheel(function(event, delta, deltaX, deltaY) {
					event.preventDefault();
					var currentScrollVal=current_obj.audio2_html5_sliderVertical.slider( "value");
					//alert (currentScrollVal+' -- '+delta);
					if ( (parseInt(currentScrollVal)>1 && parseInt(delta)==-1) || (parseInt(currentScrollVal)<100 && parseInt(delta)==1) ) {
						currentScrollVal = currentScrollVal + delta;
						current_obj.audio2_html5_sliderVertical.slider( "value", currentScrollVal);
						carouselScroll(currentScrollVal,current_obj,options,audio2_html5_thumbsHolder)
						//alert (currentScrollVal);
					}
					
				});						

					audio2_html5_thumbsHolder.css({
						'top':0+'px'
					});								  
					audio2_html5_thumbsHolder.stop().animate({
						'left': 0+'px'
					}, 400, 'easeOutQuad', function() {
						// Animation complete.
			  		});				  
			  });
			  
			  
			  
			  
		}
		
		function generatePlaylistByCateg(current_obj,options,audio2_html5_container,audio2_html5_thumbsHolder,audio2_html5_thumbsHolderWrapper,audio2_html5_thumbsHolderVisibleWrapper,audio2_html5_selectedCategDiv,audio2_html5_searchDiv,audio2_html5_playlistPadding,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_Author,audio2_html5_Audio,audio2_html5_ximage) {
			audio2_html5_thumbsHolder.stop(true,true);
			current_obj.isCarouselScrolling=false;
			
			var titleLowerCases='';
			var elementFound=false;
			var animateDur=500;
			if (current_obj.is_very_first)
				animateDur=1;
			if (current_obj.search_val!='')	
				animateDur=1;
			
			audio2_html5_thumbsHolder.stop().animate({
				  'left': (-1)*audio2_html5_thumbsHolderVisibleWrapper.width()+'px'
			}, animateDur, 'easeOutQuad', function() {
				  // Animation complete.
				  audio2_html5_thumbsHolder.html("");
				  
				  current_obj.selectedCateg_total_images=0;
				  for (var j=0;j<current_obj.playlist_arr.length;j++) {
					  elementFound=false;
					  //alert (current_obj.search_val);
					  if (current_obj.search_val!='') {
						  titleLowerCases=current_obj.playlist_arr[j]['title'].toLowerCase(); 
						  //alert (titleLowerCases.indexOf(current_obj.search_val));
						  if (titleLowerCases.indexOf(current_obj.search_val)!=-1) {
						  		elementFound=true;  
						  }
					  } else {
						  if (current_obj.playlist_arr[j]['category'].indexOf(current_obj.selectedCateg+';')!=-1) {
							  elementFound=true;  
						  }
					  }
					  
					  if (elementFound) {
						  current_obj.selectedCateg_total_images++;
						  current_obj.thumbsHolder_Thumb = $('<div class="thumbsHolder_ThumbOFF" rel="'+ (current_obj.selectedCateg_total_images-1) +'" data-origID="'+ j +'"><div class="padding">'+((options.showPlaylistNumber)?(current_obj.selectedCateg_total_images)+'. ':'')+current_obj.playlist_arr[j]['title']+'</div></div>');
						  audio2_html5_thumbsHolder.append(current_obj.thumbsHolder_Thumb);
						  if (current_obj.thumbsHolder_ThumbHeight==0) {
						  		current_obj.thumbsHolder_ThumbHeight=current_obj.thumbsHolder_Thumb.height();
						  }
						  
		  
						  current_obj.thumbsHolder_Thumb.css({
							  "top":(current_obj.thumbsHolder_ThumbHeight+1)*current_obj.selectedCateg_total_images+'px',
							  "background":options.playlistRecordBgOffColor,
							  "border-bottom-color":options.playlistRecordBottomBorderOffColor,
							  "color":options.playlistRecordTextOffColor
						  });				
						  
						  
						  
						  current_obj.current_img_no=0;
				  
						  //activate playing one
						  if (current_obj.origID==$("div[rel=\'"+(current_obj.selectedCateg_total_images-1)+"\']").attr('data-origID')){
							  current_obj.thumbsHolder_Thumb.css({
								  "background":options.playlistRecordBgOnColor,
								  "border-bottom-color":options.playlistRecordBottomBorderOnColor,
								  "color":options.playlistRecordTextOnColor
							  });
						  }
					  }
				  }
					  
				  
				  current_obj.categsAreListed=false;
				    
					  
				  /*audio2_html5_thumbsHolderWrapper.height(2*options.playlistPadding+(current_obj.thumbsHolder_ThumbHeight+1)*((options.numberOfThumbsPerScreen<current_obj.selectedCateg_total_images)?options.numberOfThumbsPerScreen:current_obj.selectedCateg_total_images)+audio2_html5_selectedCategDiv.height()+audio2_html5_searchDiv.height()+2*options.selectedCategMarginBottom+4); //current_obj.thumbsHolder_ThumbHeight+1 - 1 is the border
				  audio2_html5_thumbsHolderVisibleWrapper.height((current_obj.thumbsHolder_ThumbHeight+1)*((options.numberOfThumbsPerScreen<current_obj.selectedCateg_total_images)?options.numberOfThumbsPerScreen:current_obj.selectedCateg_total_images));	*/
				  audio2_html5_thumbsHolderWrapper.height(2*options.playlistPadding+(current_obj.thumbsHolder_ThumbHeight+1)*options.numberOfThumbsPerScreen+audio2_html5_selectedCategDiv.height()+audio2_html5_searchDiv.height()+2*options.selectedCategMarginBottom); //current_obj.thumbsHolder_ThumbHeight+1 - 1 is the border
				  audio2_html5_thumbsHolderVisibleWrapper.height((current_obj.thumbsHolder_ThumbHeight+1)*options.numberOfThumbsPerScreen);
				  audio2_html5_playlistPadding.css({'padding':options.playlistPadding+'px'});
				  
				  current_obj.thumbsHolder_Thumbs=$('.thumbsHolder_ThumbOFF', audio2_html5_container);
				  
				  
				  //the playlist scroller
				  if (current_obj.selectedCateg_total_images>options.numberOfThumbsPerScreen && options.showPlaylist) {
	
					  if (options.isPlaylistSliderInitialized) {
						  current_obj.audio2_html5_sliderVertical.slider( "destroy" );
					  }
					  current_obj.audio2_html5_sliderVertical.slider({
						  orientation: "vertical",
						  range: "min",
						  min: 1,
						  max: 100,
						  step:1,
						  value: 100,
						  slide: function( event, ui ) {
							  //alert( ui.value );
							  carouselScroll(ui.value,current_obj,options,audio2_html5_thumbsHolder);
						  }
					  });
					  options.isPlaylistSliderInitialized=true;
				  //var audio2_html5_selectedCategDiv = $('.selectedCategDiv', audio2_html5_container);
				  //var audio2_html5_searchDiv = $('.searchDiv', audio2_html5_container);
					  current_obj.audio2_html5_sliderVertical.css({
						  'display':'inline',
						  'position':'absolute',
						  'height':audio2_html5_thumbsHolderWrapper.height()-20-audio2_html5_selectedCategDiv.height()-2*options.selectedCategMarginBottom-audio2_html5_searchDiv.height()-2*options.playlistPadding+'px', // 24 is the height of  .slider-vertical.ui-slider .ui-slider-handle
						  'left':audio2_html5_container.width()+2*options.playerPadding-current_obj.audio2_html5_sliderVertical.width()-options.playlistPadding+'px',
						  'top':current_obj.audioPlayerHeight+options.playlistTopPos+options.playlistPadding+audio2_html5_selectedCategDiv.height()+options.selectedCategMarginBottom+2+'px'
					  });
					  
					  if (!options.showPlaylistOnInit)
						  current_obj.audio2_html5_sliderVertical.css({
							  'opacity': 0,
							  'display':'none'
						  });
					  options.showPlaylistOnInit=true; // to prevent sliderVertical disappereance after yo show the playlist
					  	  
					  $('.thumbsHolder_ThumbOFF', audio2_html5_container).css({
						  'width':audio2_html5_container.width()+2*options.playerPadding-current_obj.audio2_html5_sliderVertical.width()-2*options.playlistPadding-3+'px'
					  });						
	  
				  } else {
					  if (options.isPlaylistSliderInitialized) {
							current_obj.audio2_html5_sliderVertical.slider( "destroy" );
							options.isPlaylistSliderInitialized=false;
					  }
					  $('.thumbsHolder_ThumbOFF', audio2_html5_container).css({
						  'width':audio2_html5_container.width()+2*options.playerPadding-2*options.playlistPadding+'px'
					  });					
				  }	
	  
	  
				//tumbs nav
				current_obj.thumbsHolder_Thumbs.click(function() {
					if (!current_obj.is_changeSrc) {	
						options.autoPlay=true;
						var currentBut=$(this);
						var i=currentBut.attr('rel');
	
						current_obj.thumbsHolder_Thumbs.css({
							"background":options.playlistRecordBgOffColor,
							"border-bottom-color":options.playlistRecordBottomBorderOffColor,
							"color":options.playlistRecordTextOffColor
						});
						
						current_obj.current_img_no=i;
						current_obj.origID=$("div[rel=\'"+current_obj.current_img_no+"\']").attr('data-origID');
						changeSrc(current_obj,options,audio2_html5_thumbsHolder,audio2_html5_container,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_Author,audio2_html5_Audio,audio2_html5_ximage);
					}
				});	
				
				
				current_obj.thumbsHolder_Thumbs.mouseover(function() {
					var currentBut=$(this);
					currentBut.css({
						"background":options.playlistRecordBgOnColor,
						"border-bottom-color":options.playlistRecordBottomBorderOnColor,
						"color":options.playlistRecordTextOnColor
					});				
				});
				
				
				current_obj.thumbsHolder_Thumbs.mouseout(function() {
					var currentBut=$(this);
					var i=currentBut.attr('rel');
					if (current_obj.origID!=$("div[rel=\'"+i+"\']").attr('data-origID')){
						currentBut.css({
							"background":options.playlistRecordBgOffColor,
							"border-bottom-color":options.playlistRecordBottomBorderOffColor,
							"color":options.playlistRecordTextOffColor
						});
					}
				});		  
	  
				// mouse wheel
				audio2_html5_thumbsHolderVisibleWrapper.mousewheel(function(event, delta, deltaX, deltaY) {
					event.preventDefault();
					var currentScrollVal=current_obj.audio2_html5_sliderVertical.slider( "value");
					//alert (currentScrollVal+' -- '+delta);
					if ( (parseInt(currentScrollVal)>1 && parseInt(delta)==-1) || (parseInt(currentScrollVal)<100 && parseInt(delta)==1) ) {
						currentScrollVal = currentScrollVal + delta;
						current_obj.audio2_html5_sliderVertical.slider( "value", currentScrollVal);
						carouselScroll(currentScrollVal,current_obj,options,audio2_html5_thumbsHolder)
						//alert (currentScrollVal);
					}
					
				});		  


				audio2_html5_thumbsHolder.css({
					'top':0+'px'
				});
				audio2_html5_thumbsHolder.stop().animate({
					'left': 0+'px'
				}, 400, 'easeOutQuad', function() {
					// Animation complete.
				});		
				
			
			});
		}


		function findNextVideoNumbers(current_obj,options,navigationFlag) {
				if (options.shuffle) {
					var new_current_img_no=Math.floor(Math.random() * (current_obj.selectedCateg_total_images-1));
					if (new_current_img_no!=current_obj.current_img_no) {
						current_obj.current_img_no=new_current_img_no;
					} else {
						current_obj.current_img_no=Math.floor(Math.random() * (current_obj.selectedCateg_total_images-1));
					}					
				} else {
					if (navigationFlag=='next') {
						if (current_obj.current_img_no==current_obj.selectedCateg_total_images-1)
							current_obj.current_img_no=0;
						else
							current_obj.current_img_no++;
					} else {
						if (current_obj.current_img_no-1<0)
							current_obj.current_img_no=current_obj.selectedCateg_total_images-1;
						else
							current_obj.current_img_no--;
					}
				}

				current_obj.origID=$("div[rel=\'"+current_obj.current_img_no+"\']").attr('data-origID');				
		};


		function getInternetExplorerVersion()
		// -1 - not IE
		// 7,8,9 etc
		{
		   var rv = -1; // Return value assumes failure.
		   if (navigator.appName == 'Microsoft Internet Explorer')
		   {
			  var ua = navigator.userAgent;
			  var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
			  if (re.exec(ua) != null)
				 rv = parseFloat( RegExp.$1 );
		   }
		   return parseInt(rv,10);
		}
  
		function cancelAll() {
			//alert ($("audio").attr('id'));
			//$("audio")[0].pause();				
			$("audio").each(function() {
				$('.AudioPlay').removeClass('AudioPause');
				$(this)[0].pause();
			});				
		}

		
		
	//core
	$.fn.audio2_html5 = function(options) {
		
		var options = $.extend({},$.fn.audio2_html5.defaults, options);
		//parse it
		return this.each(function() {
			var audio2_html5_Audio = $(this);
		
			
			//the controllers
			var audio2_html5_controlsDef = $('<div class="AudioControls"> <a class="AudioRewind" title="Rewind"></a><a class="AudioShuffle" title="Shuffle Playlist"></a><a class="AudioDownload" title="Download File"></a><a class="AudioFacebook" title="Facebook"></a><a class="AudioTwitter" title="Twitter"></a><a class="AudioPlay" title="Play/Pause"></a><a class="AudioPrev" title="Previous"></a><a class="AudioNext" title="Next"></a><a class="AudioShowHidePlaylist" title="Show/Hide Playlist"></a><a class="VolumeButton" title="Mute/Unmute"></a><div class="VolumeSlider"></div> <div class="AudioTimer_a">00:00</div><div class="AudioTimer_b">00:00</div>  </div>   <div class="AudioBuffer"></div><div class="AudioSeek"></div><div class="songTitle"><div class="songTitleInside"></div></div>  <div class="songAuthor"></div>   <div class="bordersDiv"></div>   <div class="thumbsHolderWrapper"><div class="playlistPadding"><div class="selectedCategDiv"><div class="innerSelectedCategDiv">CATEGORIES</div></div> <div class="thumbsHolderVisibleWrapper"><div class="thumbsHolder"></div></div><div class="searchDiv"><input class="search_term" type="text" value="search..." /></div></div></div>  <div class="slider-vertical"></div> <div class="ximage"></div>  <div class="bordersDiv"></div> ');						
		
					
			
			//the elements
			var audio2_html5_container = audio2_html5_Audio.parent('.audio2_html5');
			//var audio2_html5_border = $(this).parent();
			//alert (audio2_html5_border.attr('class')+'   ---   '+audio2_html5_container.attr('class'));  // the same

			audio2_html5_container.addClass(options.skin);
			audio2_html5_container.append(audio2_html5_controlsDef);					
			
			var audio2_html5_controls = $('.AudioControls', audio2_html5_container);
			var audio2_html5_rewind_btn = $('.AudioRewind', audio2_html5_container);
			var audio2_html5_shuffle_btn = $('.AudioShuffle', audio2_html5_container);
			var audio2_html5_download_btn = $('.AudioDownload', audio2_html5_container);
			var audio2_html5_facebook_btn = $('.AudioFacebook', audio2_html5_container);
			var audio2_html5_twitter_btn = $('.AudioTwitter', audio2_html5_container);
			var audio2_html5_play_btn = $('.AudioPlay', audio2_html5_container);
			var audio2_html5_prev_btn = $('.AudioPrev', audio2_html5_container);
			var audio2_html5_next_btn = $('.AudioNext', audio2_html5_container);
			var audio2_html5_bordersDiv = $('.bordersDiv', audio2_html5_container);
			var audio2_html5_showHidePlaylist_btn = $('.AudioShowHidePlaylist', audio2_html5_container);
			var audio2_html5_volumeMute_btn = $('.VolumeButton', audio2_html5_container);
			var audio2_html5_volumeSlider = $('.VolumeSlider', audio2_html5_container);
			var audio2_html5_Audio_timer_a = $('.AudioTimer_a', audio2_html5_container);
			var audio2_html5_Audio_timer_b = $('.AudioTimer_b', audio2_html5_container);
			var audio2_html5_Title = $('.songTitle', audio2_html5_container);
			var audio2_html5_TitleInside = $('.songTitleInside', audio2_html5_container);
			var audio2_html5_Author = $('.songAuthor', audio2_html5_container);
			var audio2_html5_ximage = $('.ximage', audio2_html5_container);
			
			
			var audio2_html5_Audio_buffer = $('.AudioBuffer', audio2_html5_container);
			var audio2_html5_Audio_seek = $('.AudioSeek', audio2_html5_container);
			
			var ver_ie=getInternetExplorerVersion();
			
			
			

			
			//initilize the player with the options
			audio2_html5_container.css({
				'background':options.playerBg,
				'padding':options.playerPadding+'px'
			});
			
			
			/****if (val.indexOf("ipad") != -1 || val.indexOf("iphone") != -1 || val.indexOf("ipod") != -1 || val.indexOf("webos") != -1) {
				//audio2_html5_controls.css({margin-top:-20px;});
				audio2_html5_container.css({
					'padding-top':'0px'
				});
			}****/
			
			var current_obj = {
				current_img_no:0,
				origID:0,
				is_very_first:true,
				total_images:0,
				selectedCateg_total_images:0,
				numberOfCategories:0,
				is_seeking:false,
				is_changeSrc:false,
				is_buffer_complete:false,
				timeupdateInterval:'',
				totalTime:'',
				playlist_arr:'',
				isCarouselScrolling:false,
				isAuthorTitleInsideScrolling:false,
				curSongText:'',
				authorTitleInsideWait:0,
				audioPlayerWidth:0,
				audioPlayerHeight:0,
				seekBarLeftRightSpacing:15,
				
				category_arr:'',
				selectedCateg:'',
				categsAreListed:false,
				thumbsHolder_Thumb:$('<div class="thumbsHolder_ThumbOFF" rel="0"><div class="padding">test</div></div>'),
				thumbsHolder_ThumbHeight:0,
				thumbsHolder_Thumbs:'',
				
				search_val:'',
				
				constantDistance:18,
				timerTopPos:0,
				timerLeftPos:0,
				bufferTopPos:0,
				bufferLeftPos:0,
				titleWidth:0,
				authorTopPos:0,
				authorLeftPos:0,
				titleTopPos:0,
				titleLeftPos:0,	
				imageTopPos:0,
				imageLeftPos:0,
				playTopPos:0,
				playLeftPos:0,
				previousTopPos:0,
				previousLeftPos:0,
				nextTopPos:0,
				nextLeftPos:0,
				volumeTopPos:0,
				volumeLeftPos:0,
				volumesliderTopPos:0,
				volumesliderLeftPos:0,
				bordersdivTopPos:0,
				bordersdivLeftPos:0,
				showhideplaylistTopPos:0,
				showhideplaylistLeftPos:0,
				rewindTopPos:0,
				rewindLeftPos:0,
				shuffleTopPos:0,
				shuffleLeftPos:0,
				downloadTopPos:0,
				downloadLeftPos:0,
				facebookTopPos:0,
				facebookLeftPos:0,
				twitterTopPos:0,
				twitterLeftPos:0,
				
				origParentFloat:'',
				origParentPaddingTop:'',
				origParentPaddingRight:'',
				origParentPaddingBottom:'',
				origParentPaddingLeft:'',
				
				windowWidth:0,
				
				audioID:'',
				audioObj:''
			};
			current_obj.audioID=audio2_html5_Audio.attr('id');			


				
			if (!options.showPlaylistBut) {
				audio2_html5_showHidePlaylist_btn.css({
					'display':'none',
					'width':0,
					'height':0,
					'padding':0,
					'margin':0
				});
			}
				
			
			
			
			/*current_obj.timerTopPos=parseInt(audio2_html5_Audio_timer_a.css('top').substring(0, audio2_html5_Audio_timer_a.css('top').length-2),10);
			current_obj.timerLeftPos=parseInt(audio2_html5_Audio_timer_a.css('left').substring(0, audio2_html5_Audio_timer_a.css('left').length-2),10);*/
			current_obj.timerTopPos=10;
			current_obj.timerLeftPos=current_obj.constantDistance;
			audio2_html5_Audio_timer_a.css({
				'color':options.timerColor,
				'top':current_obj.timerTopPos+'px',
				'left':current_obj.timerLeftPos+'px'		
			});
			audio2_html5_Audio_timer_b.css({
				'color':options.timerColor,
				'top':current_obj.timerTopPos+'px',
				'right':current_obj.timerLeftPos+'px'				
			});

			


			
			//options.playlistTopPos=0;
			/*****if (val.indexOf("android") != -1) {
				options.playlistTopPos-=0;
			} else if (val.indexOf("ipad") != -1 || val.indexOf("iphone") != -1 || val.indexOf("ipod") != -1 || val.indexOf("webos") != -1) {
				audio2_html5_controls.css('margin-top','-9px');
				options.playlistTopPos-=5;
			}*****/

						
			//audio2_html5_border.width(options.playerWidth+10);
			audio2_html5_container.width(options.playerWidth);
			options.origWidth=options.playerWidth;
			
			/***if (!options.showSeekBar) {
				audio2_html5_container.height(audio2_html5_container.height()-4);
			}
			
			if (!options.showAuthor && !options.showTitle) {
				audio2_html5_container.height(audio2_html5_container.height()-22);
			}***/
			
		
		
			
			
			
			//the image
			audio2_html5_ximage.css({
				'top':current_obj.timerTopPos+audio2_html5_Audio_timer_a.height()+current_obj.constantDistance+'px',
				'left':current_obj.timerLeftPos+'px'				
			});
			
			current_obj.imageTopPos=parseInt(audio2_html5_ximage.css('top').substring(0, audio2_html5_ximage.css('top').length-2),10);
			current_obj.imageLeftPos=parseInt(audio2_html5_ximage.css('left').substring(0, audio2_html5_ximage.css('left').length-2),10);

			//author & title 
			audio2_html5_Title.css({'color':options.songTitleColor});
			audio2_html5_Author.css({'color':options.songAuthorColor});
			current_obj.titleWidth=options.playerWidth-current_obj.timerLeftPos-audio2_html5_ximage.width()-2*current_obj.constantDistance;

			current_obj.authorTopPos=current_obj.imageTopPos+2;
			current_obj.authorLeftPos=current_obj.imageLeftPos+audio2_html5_ximage.width()+current_obj.constantDistance;

			current_obj.titleTopPos=parseInt(audio2_html5_ximage.css('top').substring(0, audio2_html5_ximage.css('top').length-2),10)+audio2_html5_Author.height()+8;
			current_obj.titleLeftPos=parseInt(audio2_html5_ximage.css('left').substring(0, audio2_html5_ximage.css('left').length-2),10)+audio2_html5_ximage.width()+current_obj.constantDistance;
			
			audio2_html5_Author.css({
				'top':current_obj.authorTopPos+'px',
				'left':current_obj.authorLeftPos+'px'
			});

			audio2_html5_Title.css({
				'top':current_obj.titleTopPos+'px',
				'left':current_obj.titleLeftPos+'px'
			});		
			
			//play, next, prev buttons
			current_obj.playTopPos=current_obj.imageTopPos+audio2_html5_ximage.height()-audio2_html5_play_btn.height();
			current_obj.playLeftPos=current_obj.imageLeftPos+audio2_html5_ximage.width()+current_obj.constantDistance+audio2_html5_prev_btn.width()+parseInt(current_obj.constantDistance/2,10);			
			audio2_html5_play_btn.css({
				'top':current_obj.playTopPos+'px',
				'left':current_obj.playLeftPos+'px'
			});

			current_obj.previousTopPos=current_obj.playTopPos+parseInt((audio2_html5_play_btn.height()-audio2_html5_prev_btn.height())/2,10);
			current_obj.previousLeftPos=current_obj.imageLeftPos+audio2_html5_ximage.width()+current_obj.constantDistance;				
			audio2_html5_prev_btn.css({
				'top':current_obj.previousTopPos+'px',
				'left':current_obj.previousLeftPos+'px'
			});
			
			current_obj.nextTopPos=current_obj.previousTopPos;
			current_obj.nextLeftPos=current_obj.playLeftPos+audio2_html5_play_btn.width()+parseInt(current_obj.constantDistance/2,10);				
			audio2_html5_next_btn.css({
				'top':current_obj.nextTopPos+'px',
				'left':current_obj.nextLeftPos+'px'
			});
			
			
			//volume
			current_obj.volumeTopPos=current_obj.nextTopPos+parseInt((audio2_html5_next_btn.height()-audio2_html5_volumeMute_btn.height())/2,10);
			current_obj.volumeLeftPos=current_obj.nextLeftPos+audio2_html5_next_btn.width()+parseInt(current_obj.constantDistance*1.5,10);
			audio2_html5_volumeMute_btn.css({
				'top':current_obj.volumeTopPos+'px',
				'left':current_obj.volumeLeftPos+'px'
			});
			current_obj.volumesliderTopPos=current_obj.volumeTopPos+parseInt((audio2_html5_volumeMute_btn.height()-audio2_html5_volumeSlider.height())/2,10);
			current_obj.volumesliderLeftPos=current_obj.volumeLeftPos+audio2_html5_volumeMute_btn.width()+parseInt(current_obj.constantDistance/2,10);
			audio2_html5_volumeSlider.css({
				'top':current_obj.volumesliderTopPos+'px',
				'left':current_obj.volumesliderLeftPos+'px'
			});		

			
			//bordersDiv
			current_obj.bordersdivTopPos=current_obj.imageTopPos+audio2_html5_ximage.height()+current_obj.constantDistance;
			current_obj.bordersdivLeftPos=current_obj.constantDistance;
			audio2_html5_bordersDiv.css({
				'width':options.playerWidth-2*current_obj.constantDistance+'px',
				'border-top-color':options.bordersDivColor,
				'border-bottom-color':options.bordersDivColor,
				'top':current_obj.bordersdivTopPos+'px',
				'left':current_obj.bordersdivLeftPos+'px'
			});	
			
			
			// set player height
			current_obj.audioPlayerHeight=current_obj.bordersdivTopPos+audio2_html5_bordersDiv.height()+current_obj.constantDistance;	
			audio2_html5_container.height(current_obj.audioPlayerHeight);
			
			
			//show/hide playlist
			current_obj.showhideplaylistTopPos=current_obj.bordersdivTopPos+parseInt((audio2_html5_bordersDiv.height()-audio2_html5_showHidePlaylist_btn.height())/2,10);
			current_obj.showhideplaylistLeftPos=current_obj.constantDistance+3;
			audio2_html5_showHidePlaylist_btn.css({
				'top':current_obj.showhideplaylistTopPos+'px',
				'right':current_obj.showhideplaylistLeftPos+'px'
			});
			
			//rewind
			current_obj.rewindTopPos=current_obj.bordersdivTopPos+parseInt((audio2_html5_bordersDiv.height()-audio2_html5_rewind_btn.height())/2,10);
			current_obj.rewindLeftPos=current_obj.constantDistance+3;
			audio2_html5_rewind_btn.css({
				'top':current_obj.rewindTopPos+'px',
				'left':current_obj.rewindLeftPos+'px'
			});
			if (!options.showRewindBut) {				
				audio2_html5_rewind_btn.css({
					'display':'none',
					'width':0,
					'height':0,
					'padding':0,
					'margin':0
				});
				current_obj.rewindLeftPos=0;
			}
			
			//shuffle
			current_obj.shuffleTopPos=current_obj.bordersdivTopPos+parseInt((audio2_html5_bordersDiv.height()-audio2_html5_shuffle_btn.height())/2,10);
			current_obj.shuffleLeftPos=current_obj.rewindLeftPos+audio2_html5_rewind_btn.width()+current_obj.constantDistance;
			audio2_html5_shuffle_btn.css({
				'top':current_obj.shuffleTopPos+'px',
				'left':current_obj.shuffleLeftPos+'px'
			});
			if (options.shuffle) {
				audio2_html5_shuffle_btn.addClass('AudioShuffleON');
			}
			if (!options.showShuffleBut) {				
				audio2_html5_shuffle_btn.css({
					'display':'none',
					'width':0,
					'height':0,
					'padding':0,
					'margin':0
				});
				current_obj.shuffleLeftPos=current_obj.rewindLeftPos+audio2_html5_rewind_btn.width();
			}			
			
			//download
			current_obj.downloadTopPos=current_obj.bordersdivTopPos+parseInt((audio2_html5_bordersDiv.height()-audio2_html5_download_btn.height())/2,10);
			current_obj.downloadLeftPos=current_obj.shuffleLeftPos+audio2_html5_shuffle_btn.width()+current_obj.constantDistance;
			audio2_html5_download_btn.css({
				'top':current_obj.downloadTopPos+'px',
				'left':current_obj.downloadLeftPos+'px'
			});
			if (!options.showDownloadBut) {				
				audio2_html5_download_btn.css({
					'display':'none',
					'width':0,
					'height':0,
					'padding':0,
					'margin':0
				});
				current_obj.downloadLeftPos=current_obj.shuffleLeftPos+audio2_html5_shuffle_btn.width();
			}				
			
			//facebook
			current_obj.facebookTopPos=current_obj.bordersdivTopPos+parseInt((audio2_html5_bordersDiv.height()-audio2_html5_facebook_btn.height())/2,10);
			current_obj.facebookLeftPos=current_obj.downloadLeftPos+audio2_html5_download_btn.width()+current_obj.constantDistance;
			audio2_html5_facebook_btn.css({
				'top':current_obj.facebookTopPos+'px',
				'left':current_obj.facebookLeftPos+'px'
			});	
			if (!options.showFacebookBut) {				
				audio2_html5_facebook_btn.css({
					'display':'none',
					'width':0,
					'height':0,
					'padding':0,
					'margin':0
				});
				current_obj.facebookLeftPos=current_obj.downloadLeftPos+audio2_html5_download_btn.width();
			} else {
					  window.fbAsyncInit = function() {
						FB.init({
						  appId:options.facebookAppID,
						  version:'v2.0',
						  status:true,
						  cookie:true,
						  xfbml:true
						});
					  };
				
					  (function(d, s, id){
						 var js, fjs = d.getElementsByTagName(s)[0];
						 if (d.getElementById(id)) {return;}
						 js = d.createElement(s); js.id = id;
						 js.src = "//connect.facebook.com/en_US/sdk.js";
						 fjs.parentNode.insertBefore(js, fjs);
					   }(document, 'script', 'facebook-jssdk'));				
			}
			audio2_html5_facebook_btn.click(function() {
				var imageLink=current_obj.playlist_arr[current_obj.origID]['image'];
				var pathArray = window.location.pathname.split( '/' );
				if (imageLink.indexOf('http://')!=-1 || imageLink.indexOf('https://')!=-1) {
					//imageLink=current_obj.playlist_arr[current_obj.origID]['image'];	
				} else {
					if (pathArray[pathArray.length-1].indexOf('.')!=-1) {
						pathArray.pop(); 
					}
					imageLink=window.location.protocol+'//'+window.location.host+'/'+pathArray.join('/')+'/'+current_obj.playlist_arr[current_obj.origID]['image'];
				}
				//alert (imageLink);
				FB.ui(
				  {
				   method: 'feed',
				   name: options.facebookShareTitle,
				   caption: current_obj.playlist_arr[current_obj.origID]['title'],
				   description: options.facebookShareDescription,
				   link: document.URL,
				   picture: imageLink
				  },
				  function(response) {
					/*if (response && response.post_id) {
					  alert('Post was published.');
					} else {
					  alert('Post was not published.');
					}*/
				  }
				);
			});				
			
			
			
			//twitter
			current_obj.twitterTopPos=current_obj.bordersdivTopPos+parseInt((audio2_html5_bordersDiv.height()-audio2_html5_twitter_btn.height())/2,10);
			current_obj.twitterLeftPos=current_obj.facebookLeftPos+audio2_html5_facebook_btn.width()+current_obj.constantDistance;
			audio2_html5_twitter_btn.css({
				'top':current_obj.twitterTopPos+'px',
				'left':current_obj.twitterLeftPos+'px'
			});						
			if (!options.showTwitterBut) {				
				audio2_html5_twitter_btn.css({
					'display':'none',
					'width':0,
					'height':0,
					'padding':0,
					'margin':0
				});
				current_obj.twitterLeftPos=current_obj.facebookLeftPos+audio2_html5_facebook_btn.width();
			} else {
				/*window.twttr = (function (d,s,id) {			
				  var t, js, fjs = d.getElementsByTagName(s)[0];			
				  if (d.getElementById(id)) return; js=d.createElement(s); js.id=id;			
				  js.src="https://platform.twitter.com/widgets.js"; fjs.parentNode.insertBefore(js, fjs);			
				  return window.twttr || (t = { _e: [], ready: function(f){ t._e.push(f) } });			
				}(document, "script", "twitter-wjs"));*/
			}
			audio2_html5_twitter_btn.click(function() {
				var myURL = "http://www.google.com";
        		window.open("https://twitter.com/intent/tweet?url=" + document.URL+ "&text="+current_obj.playlist_arr[current_obj.origID]['title'],"Twitter","status = 1, left = 430, top = 270, height = 550, width = 420, resizable = 0");
			});					
			
			
			
			//generate playlist
			var currentCarouselTop=0;
			var audio2_html5_thumbsHolderWrapper = $('.thumbsHolderWrapper', audio2_html5_container);
			var audio2_html5_playlistPadding = $('.playlistPadding', audio2_html5_container);
			var audio2_html5_thumbsHolderVisibleWrapper = $('.thumbsHolderVisibleWrapper', audio2_html5_container);
			var audio2_html5_thumbsHolder = $('.thumbsHolder', audio2_html5_container);
			current_obj.audio2_html5_sliderVertical = $('.slider-vertical', audio2_html5_container);
			var audio2_html5_selectedCategDiv = $('.selectedCategDiv', audio2_html5_container);
			var audio2_html5_innerSelectedCategDiv = $('.innerSelectedCategDiv', audio2_html5_container);
			var audio2_html5_searchDiv = $('.searchDiv', audio2_html5_container);
			var audio2_html5_search_term = $('.search_term', audio2_html5_container);
			
			if (!options.showPlaylist) {
				//audio2_html5_thumbsHolderWrapper.css({'display':'none'});
				audio2_html5_thumbsHolderWrapper.css({
					'opacity':0,
					'visibility':'hidden'
				});
			}			
			
			if (!options.showPlaylistOnInit) {
				audio2_html5_thumbsHolderWrapper.css({
					    'opacity': 0,
						'visibility':'hidden',
						'margin-top':'-20px'/*,
						'display':'none'*/
				});
			}
			
			if (!options.showCategories)	{
				audio2_html5_selectedCategDiv.css({
					'visibility':'hidden',
					'height':0
				});				
			}						
			
			audio2_html5_selectedCategDiv.css({
				'background-color':options.selectedCategBg,
				'background-position':'10px 50%',
				'margin-bottom':options.selectedCategMarginBottom+'px'		
			});
			audio2_html5_innerSelectedCategDiv.css({
				'color':options.selectedCategOffColor,
				'background-position':(options.playerWidth-2*options.playlistPadding-20)+'px 50%'
			});		
			
			if (!options.showSearchArea)	{
				audio2_html5_searchDiv.css({
					'visibility':'hidden',
					'height':0
				});				
			}
			
			
			audio2_html5_searchDiv.css({
				'background-color':options.searchAreaBg,
				'margin-top':options.selectedCategMarginBottom+'px'		
			});

			audio2_html5_search_term.val(options.searchInputText);
			audio2_html5_search_term.css({
				'width':parseInt((options.playerWidth-2*options.playlistPadding)/2,10)+'px',
				'background-color':options.searchInputBg,
				'border-color':options.searchInputBorderColor,
				'color':options.searchInputTextColor
			});

			audio2_html5_thumbsHolderWrapper.css({
				'width':audio2_html5_container.width()+2*options.playerPadding+'px',
				'top':current_obj.audioPlayerHeight+options.playlistTopPos+'px',
				'left':'0px',
				'background':options.playlistBgColor
				
			});
			
			audio2_html5_thumbsHolderVisibleWrapper.width(audio2_html5_container.width());

			
			current_obj.playlist_arr=new Array();
			current_obj.category_arr=new Array();
			var resultsSplit_arr=new Array();
			
			var playlistElements = $('.xaudioplaylist', audio2_html5_container).children();
			playlistElements.each(function() { // ul-s
	            currentElement = $(this);
	            current_obj.total_images++;
	            current_obj.playlist_arr[current_obj.total_images-1]=new Array();
	            current_obj.playlist_arr[current_obj.total_images-1]['title']='';
	            current_obj.playlist_arr[current_obj.total_images-1]['author']='';
	            current_obj.playlist_arr[current_obj.total_images-1]['image']='';
				current_obj.playlist_arr[current_obj.total_images-1]['category']='';
	            current_obj.playlist_arr[current_obj.total_images-1]['sources_mp3']='';
	            current_obj.playlist_arr[current_obj.total_images-1]['sources_ogg']='';
	            
	            //alert (currentElement.find('.xtitle').html())
	            if (currentElement.find('.xtitle').html()!=null) {
	            	current_obj.playlist_arr[current_obj.total_images-1]['title']=currentElement.find('.xtitle').html();
	            }	            
	            
	            if (currentElement.find('.xauthor').html()!=null) {
	            	current_obj.playlist_arr[current_obj.total_images-1]['author']=currentElement.find('.xauthor').html();
	            }
	            
	            if (currentElement.find('.ximage').html()!=null) {
	            	current_obj.playlist_arr[current_obj.total_images-1]['image']=currentElement.find('.ximage').html();
	            }
				
				if (currentElement.find('.xcategory').html()!=null) {
	            	current_obj.playlist_arr[current_obj.total_images-1]['category']=currentElement.find('.xcategory').html()+';';

				   resultsSplit_arr = current_obj.playlist_arr[current_obj.total_images-1]['category'].split(';');
				   for (var j=0;j<resultsSplit_arr.length;j++) {
					  if (current_obj.category_arr.indexOf(resultsSplit_arr[j])===-1 && resultsSplit_arr[j]!='') {
						  current_obj.category_arr.push(resultsSplit_arr[j]);
					  }
				   }					
	            }
				

	            if (currentElement.find('.xsources_mp3').html()!=null) {
	            	current_obj.playlist_arr[current_obj.total_images-1]['sources_mp3']=currentElement.find('.xsources_mp3').html();
	            }	  
	            
	            if (currentElement.find('.xsources_ogg').html()!=null) {
	            	current_obj.playlist_arr[current_obj.total_images-1]['sources_ogg']=currentElement.find('.xsources_ogg').html();
	            }

			});	
			
			current_obj.numberOfCategories=current_obj.category_arr.length;
			current_obj.category_arr.sort();
			current_obj.selectedCateg=options.firstCateg;
			if (options.firstCateg=='' && current_obj.category_arr.indexOf(options.firstCateg)===-1) {
				current_obj.selectedCateg=current_obj.category_arr[0];
			}
			audio2_html5_innerSelectedCategDiv.html(current_obj.selectedCateg);
            //generate playlist for the first time
			generatePlaylistByCateg(current_obj,options,audio2_html5_container,audio2_html5_thumbsHolder,audio2_html5_thumbsHolderWrapper,audio2_html5_thumbsHolderVisibleWrapper,audio2_html5_selectedCategDiv,audio2_html5_searchDiv,audio2_html5_playlistPadding,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_Author,audio2_html5_Audio,audio2_html5_ximage);
			

//alert (audio2_html5_container.css("top"));
			
			
			
			//selectedCategDiv
			audio2_html5_selectedCategDiv.click(function() {
				current_obj.search_val='';
			    audio2_html5_search_term.val(options.searchInputText);

				generateCategories(current_obj,options,audio2_html5_container,audio2_html5_thumbsHolder,audio2_html5_thumbsHolderWrapper,audio2_html5_thumbsHolderVisibleWrapper,audio2_html5_selectedCategDiv,audio2_html5_innerSelectedCategDiv,audio2_html5_searchDiv,audio2_html5_playlistPadding,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_Author,audio2_html5_Audio,audio2_html5_ximage);
			});	
			
			
			
			audio2_html5_selectedCategDiv.mouseover(function() {
				audio2_html5_innerSelectedCategDiv.css({
					'color':options.selectedCategOnColor
				});				
			});
			
			
			audio2_html5_selectedCategDiv.mouseout(function() {
				audio2_html5_innerSelectedCategDiv.css({
					'color':options.selectedCategOffColor
				});	
			});			
			
			
			
			
			
			
			//start initialize volume slider
			audio2_html5_volumeSlider.slider({
				value: options.initialVolume,
				step: 0.05,
				orientation: "horizontal",
				range: "min",
				max: 1,
				animate: true,					
				slide:function(e,ui){
						//document.getElementById(current_obj.audioID).muted=false;
						document.getElementById(current_obj.audioID).volume=ui.value;
				},
				stop:function(e,ui){
					
				}
			});
			document.getElementById(current_obj.audioID).volume=options.initialVolume;
			audio2_html5_volumeSlider.css({'background':options.volumeOffColor});
			$(".ui-slider-range",audio2_html5_volumeSlider).css({'background':options.volumeOnColor});
			//end initialize volume slider			
			
			
			
			//buttons start
			audio2_html5_play_btn.click(function() {
				var is_paused=document.getElementById(current_obj.audioID).paused;
				cancelAll();
				if (is_paused == false) {
					document.getElementById(current_obj.audioID).pause();
					audio2_html5_play_btn.removeClass('AudioPause');
				} else {	
					document.getElementById(current_obj.audioID).play();
					audio2_html5_play_btn.addClass('AudioPause');
				}
			});
			
			audio2_html5_rewind_btn.click(function() {
				document.getElementById(current_obj.audioID).currentTime=0;
				cancelAll();
				document.getElementById(current_obj.audioID).play();
				audio2_html5_play_btn.addClass('AudioPause');
				//alert (document.getElementById(current_obj.audioID).playing);
			});
			
			audio2_html5_next_btn.click(function() {
				if (!current_obj.categsAreListed) {
					if (!current_obj.is_changeSrc || current_obj.is_very_first) {
						options.autoPlay=true;
						//$(current_obj.thumbsHolder_Thumbs[current_obj.current_img_no]).removeClass('thumbsHolder_ThumbON');
						current_obj.thumbsHolder_Thumbs.css({
							"background":options.playlistRecordBgOffColor,
							"border-bottom-color":options.playlistRecordBottomBorderOffColor,
							"color":options.playlistRecordTextOffColor
						});
	
						
						findNextVideoNumbers(current_obj,options,'next');
							
											
						changeSrc(current_obj,options,audio2_html5_thumbsHolder,audio2_html5_container,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_Author,audio2_html5_Audio,audio2_html5_ximage);
					}
				}
			});
			
			audio2_html5_prev_btn.click(function() {
				if (!current_obj.categsAreListed) {
					if (!current_obj.is_changeSrc || current_obj.is_very_first) {	
						options.autoPlay=true;
						//$(current_obj.thumbsHolder_Thumbs[current_obj.current_img_no]).removeClass('thumbsHolder_ThumbON');
						current_obj.thumbsHolder_Thumbs.css({
							"background":options.playlistRecordBgOffColor,
							"border-bottom-color":options.playlistRecordBottomBorderOffColor,
							"color":options.playlistRecordTextOffColor
						});
	
						
						findNextVideoNumbers(current_obj,options,'previous');	
	
						changeSrc(current_obj,options,audio2_html5_thumbsHolder,audio2_html5_container,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_Author,audio2_html5_Audio,audio2_html5_ximage);
					}
				}
			});			
				

			audio2_html5_showHidePlaylist_btn.click(function() {
				audio2_html5_thumbsHolderWrapper.css({
						'visibility':'visible'
				});				
				if (audio2_html5_thumbsHolderWrapper.css('margin-top').substring(0, audio2_html5_thumbsHolderWrapper.css('margin-top').length-2) < 0) {
					aux_opacity=1;
					aux_display='block';
					aux_margin_top="0px";
					audio2_html5_thumbsHolderWrapper.css({
						'display':aux_display
					});
					if (current_obj.selectedCateg_total_images>options.numberOfThumbsPerScreen)
						current_obj.audio2_html5_sliderVertical.css({
							'opacity': 1,
							'display':'block'
						});
				} else {
					aux_opacity=0;
					aux_display='none';
					aux_margin_top="-20px";
					if (current_obj.selectedCateg_total_images>options.numberOfThumbsPerScreen)
						current_obj.audio2_html5_sliderVertical.css({
							'opacity': 0,
							'display':'none'
						});
				}
				
				audio2_html5_thumbsHolderWrapper.animate({
					    'opacity': aux_opacity,
						'margin-top':aux_margin_top

					  }, 500, 'easeOutQuad', function() {
					    // Animation complete.
						audio2_html5_thumbsHolderWrapper.css({
							'display':aux_display
						});
					});				
			});
			
			audio2_html5_volumeMute_btn.click(function() {
				if (!document.getElementById(current_obj.audioID).muted) {
					document.getElementById(current_obj.audioID).muted=true;
					audio2_html5_volumeMute_btn.addClass('VolumeButtonMuted');
				} else {
					document.getElementById(current_obj.audioID).muted=false;
					audio2_html5_volumeMute_btn.removeClass('VolumeButtonMuted');
				}
			});
			
			
			audio2_html5_shuffle_btn.click(function() {
				if (options.shuffle) {
					audio2_html5_shuffle_btn.removeClass('AudioShuffleON');
					options.shuffle=false;
				} else {
					audio2_html5_shuffle_btn.addClass('AudioShuffleON');
					options.shuffle=true;	
				}
			});
			
			audio2_html5_download_btn.click(function() {
				//alert (current_obj.playlist_arr[current_obj.origID]['sources_mp3']);
				window.open(options.pathToDownloadFile+"download.php?the_file="+current_obj.playlist_arr[current_obj.origID]['sources_mp3']);
			});

			//buttons end
			
			
			
			audio2_html5_thumbsHolder.swipe( {
				swipeStatus:function(event, phase, direction, distance, duration, fingerCount)
				{
					//$('#logulmeu').html("phase: "+phase+"<br>direction: "+direction+"<br>distance: "+distance);
					if (direction=='up' || direction=='down') {
						if (distance!=0) {
							  currentScrollVal=current_obj.audio2_html5_sliderVertical.slider( "value");
							  if (direction=="up") {
									currentScrollVal = currentScrollVal - 1.5;
							  } else {
									currentScrollVal = currentScrollVal + 1.5;
							  }
							  current_obj.audio2_html5_sliderVertical.slider( "value", currentScrollVal);
							  carouselScroll(currentScrollVal,current_obj,options,audio2_html5_thumbsHolder);
						}	  
					}
				  
				  //Here we can check the:
				  //phase : 'start', 'move', 'end', 'cancel'
				  //direction : 'left', 'right', 'up', 'down'
				  //distance : Distance finger is from initial touch point in px
				  //duration : Length of swipe in MS 
				  //fingerCount : the number of fingers used
				  },
				  
				  threshold:100,
				  maxTimeThreshold:500,
				  fingers:'all'
			});
			
			
			
			
			//search area functions
			audio2_html5_search_term.on('click', function() {
				$(this).val('');
			});
			audio2_html5_search_term.on('input', function() {
				//alert( $(this).val() );
				current_obj.search_val=audio2_html5_search_term.val().toLowerCase();
				generatePlaylistByCateg(current_obj,options,audio2_html5_container,audio2_html5_thumbsHolder,audio2_html5_thumbsHolderWrapper,audio2_html5_thumbsHolderVisibleWrapper,audio2_html5_selectedCategDiv,audio2_html5_searchDiv,audio2_html5_playlistPadding,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_Author,audio2_html5_Audio,audio2_html5_ximage);
			});
			
			
			
			//audio ended
			document.getElementById(current_obj.audioID).addEventListener('ended',function (){endAudioHandler(current_obj,options,audio2_html5_container,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_next_btn,audio2_html5_Audio)
			});
			
			//google analytics
			if (options.googleTrakingOn) {
				ga('create', options.googleTrakingCode, 'auto');
			}			
		    	
			
			//initialize first Audio
			changeSrc(current_obj,options,audio2_html5_thumbsHolder,audio2_html5_container,audio2_html5_play_btn,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_Author,audio2_html5_Audio,audio2_html5_ximage);
			current_obj.timeupdateInterval=setInterval(function(){
					//alert (document.getElementById(current_obj.audioID).currentTime);
					seekUpdate(current_obj,options,audio2_html5_container,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_play_btn,audio2_html5_Audio,audio2_html5_Title,audio2_html5_TitleInside,audio2_html5_Author);
    		},300);	
			
			document.getElementById(current_obj.audioID).addEventListener("durationchange", function() {
				if (current_obj.is_changeSrc) {
					current_obj.totalTime = document.getElementById(current_obj.audioID).duration;
				}
			});
			
			if (val.indexOf("ipad") != -1 || val.indexOf("iphone") != -1 || val.indexOf("ipod") != -1 || val.indexOf("webos") != -1) {
				current_obj.totalTime=0;
				document.getElementById(current_obj.audioID).addEventListener("canplaythrough", function() {
					if (current_obj.totalTime != document.getElementById(current_obj.audioID).duration) {
						//seekbar init
						if (options.isSliderInitialized) {
							audio2_html5_Audio_seek.slider("destroy");
							options.isSliderInitialized=false;
						}
						if (options.isProgressInitialized) {
							audio2_html5_Audio_buffer.progressbar("destroy");
							options.isProgressInitialized=false;
						}
				
						current_obj.totalTime = document.getElementById(current_obj.audioID).duration;
						generate_seekBar(current_obj,options,audio2_html5_container,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_play_btn,audio2_html5_Audio);
						if (options.isProgressInitialized) {	
							audio2_html5_Audio_buffer.progressbar({ value: options.playerWidth });
						}
					}
				});	
			}
			
			
			
			var doResize = function() {
				  if (current_obj.origParentFloat=='') {
					  current_obj.origParentFloat=audio2_html5_container.parent().css('float');
					  current_obj.origParentPaddingTop=audio2_html5_container.parent().css('padding-top');
					  current_obj.origParentPaddingRight=audio2_html5_container.parent().css('padding-right');
					  current_obj.origParentPaddingBottom=audio2_html5_container.parent().css('padding-bottom');
					  current_obj.origParentPaddingLeft=audio2_html5_container.parent().css('padding-left');
				  }		
				  
				  //alert (options.playerWidth+'  !=    '+options.origWidth +'   ||   '+options.playerWidth+'   >    '+$(window).width());
				  
				  if (options.playerWidth!=options.origWidth || options.playerWidth>$(window).width()) {
						  audio2_html5_container.parent().css({
							  'float':'none',
							  'padding-top':0,
							  'padding-right':0,
							  'padding-bottom':0,
							  'padding-left':0
						  });							  
				  } else {
					  audio2_html5_container.parent().css({
						  'float':current_obj.origParentFloat,
						  'padding-top':current_obj.origParentPaddingTop,
						  'padding-right':current_obj.origParentPaddingRight,
						  'padding-bottom':current_obj.origParentPaddingBottom,
						  'padding-left':current_obj.origParentPaddingLeft
					  });
				  }				
				/*audio2_html5_container.parent().css({
						  'float':'none'
					  });*/
				
				  var responsiveWidth=audio2_html5_container.parent().width();

				  var new_buffer_width;
				  //var responsiveHeight=audio2_html5_container.parent().height();
				  
				  
				  
				  /*if (options.responsiveRelativeToBrowser) {
					  responsiveWidth=$(window).width();
					  responsiveHeight=$(window).height();
				  }*/
				  
				  
					//AAAA//
					if (options.playerWidth<385) {
						  audio2_html5_volumeSlider.css ({
							  'display':'none'	
						  });
					} else {
						  audio2_html5_volumeSlider.css ({
							  'display':'block'	
						  });							  
					}
					
					if (options.playerWidth<270) {
						  audio2_html5_volumeMute_btn.css ({
							  'display':'none'	
						  });
					} else {
						  audio2_html5_volumeMute_btn.css ({
							  'display':'block'	
						  });							  
					}

					if (options.playerWidth<240) {
						 audio2_html5_next_btn.css ({
							  'display':'none'	
						  });
					} else {
						  audio2_html5_next_btn.css ({
							  'display':'block'	
						  });							  
					}
					//AAAA//		  
				  
  
							  
					if (audio2_html5_container.width()!=responsiveWidth) {
						//alert (audio2_html5_container.width()+"!="+responsiveWidth);
						  if (options.origWidth>responsiveWidth) {
							  options.playerWidth=responsiveWidth;
						  } else {
							  options.playerWidth=options.origWidth;
						  }
						  
						  //AAAA//
						  if (options.playerWidth<385) {
								audio2_html5_volumeSlider.css ({
									'display':'none'	
								});
						  } else {
								audio2_html5_volumeSlider.css ({
									'display':'block'	
								});							  
						  }
						  
						  if (options.playerWidth<270) {
								audio2_html5_volumeMute_btn.css ({
									'display':'none'	
								});
						  } else {
								audio2_html5_volumeMute_btn.css ({
									'display':'block'	
								});							  
						  }
	  
						  if (options.playerWidth<240) {
							   audio2_html5_next_btn.css ({
									'display':'none'	
								});
						  } else {
								audio2_html5_next_btn.css ({
									'display':'block'	
								});							  
						  }		
						  //AAAA//				  
						  
 						  //alert(audio2_html5_container.width()+' -- '+responsiveWidth+' -- '+options.playerWidth);
						  if (audio2_html5_container.width()!=options.playerWidth) {
						  		 audio2_html5_container.width(options.playerWidth);
						  										//generate_seekBar(current_obj,options,audio2_html5_container,audio2_html5_Audio_seek,audio2_html5_Audio_buffer,audio2_html5_Audio_timer_a,audio2_html5_Audio_timer_b,audio2_html5_play_btn,audio2_html5_Audio);
								  new_buffer_width=options.playerWidth-2*current_obj.timerLeftPos-2*audio2_html5_Audio_timer_a.width()-2*current_obj.seekBarLeftRightSpacing;
								  audio2_html5_Audio_buffer.width(new_buffer_width);
								  audio2_html5_Audio_seek.width(new_buffer_width);

								  audio2_html5_bordersDiv.css({
									'width':options.playerWidth-2*current_obj.constantDistance+'px'
								  });	
								  current_obj.titleWidth=options.playerWidth-current_obj.timerLeftPos-audio2_html5_ximage.width()-2*current_obj.constantDistance;
								  
								  audio2_html5_Title.width(current_obj.titleWidth);
								  audio2_html5_Author.width(current_obj.titleWidth);

								  
								  audio2_html5_thumbsHolderWrapper.width(audio2_html5_container.width()+2*options.playerPadding);
								  audio2_html5_thumbsHolderVisibleWrapper.width(audio2_html5_container.width())
								  //audio2_html5_thumbsHolder.width(audio2_html5_container.width()+2*options.playerPadding);
								  //audio2_html5_playlistPadding.css({'padding':options.playlistPadding+'px'});
								  
								  //current_obj.thumbsHolder_Thumbs.width(audio2_html5_container.width()-2*options.playlistPadding);		
								  
		
								  audio2_html5_selectedCategDiv.width(options.playerWidth-2*options.playlistPadding);
								  audio2_html5_innerSelectedCategDiv.css({
									  'background-position':(options.playerWidth-2*options.playlistPadding-20)+'px 50%'
								  });						  
								  
								  
								  //the playlist elements
								  if (current_obj.selectedCateg_total_images>options.numberOfThumbsPerScreen && options.showPlaylist) {
									  current_obj.audio2_html5_sliderVertical.css({
										  'left':audio2_html5_container.width()+2*options.playerPadding-current_obj.audio2_html5_sliderVertical.width()-options.playlistPadding+'px'						  							  });							  
									  $('.thumbsHolder_ThumbOFF', audio2_html5_container).css({
										  'width':audio2_html5_container.width()+2*options.playerPadding-current_obj.audio2_html5_sliderVertical.width()-2*options.playlistPadding-3+'px'
									  });						
								  } else {
									  $('.thumbsHolder_ThumbOFF', audio2_html5_container).css({
										  'width':audio2_html5_container.width()+2*options.playerPadding-2*options.playlistPadding+'px'
									  });					
								  }							  
		
		
								  audio2_html5_search_term.css({
									  'width':parseInt((options.playerWidth-2*options.playlistPadding)/2,10)+'px'
								  });
						  }

						  if (options.playerWidth<$(window).width()) {
							  audio2_html5_container.parent().css({
								  'float':current_obj.origParentFloat,
								  'padding-top':current_obj.origParentPaddingTop,
								  'padding-right':current_obj.origParentPaddingRight,
								  'padding-bottom':current_obj.origParentPaddingBottom,
								  'padding-left':current_obj.origParentPaddingLeft
							  });
						  }	


				  }
			};
			
			var TO = false;
			$(window).resize(function() {
				doResizeNow=true;
				
				if (ver_ie!=-1 && ver_ie==9 && current_obj.windowWidth==0)
					doResizeNow=false;
				
				
				if (current_obj.windowWidth==$(window).width()) {
					doResizeNow=false;
					if (options.windowCurOrientation!=window.orientation && navigator.userAgent.indexOf('Android') != -1) {
						options.windowCurOrientation=window.orientation;
						doResizeNow=true;
					}
				} else {
					/*if (current_obj.windowWidth===0 && (val.indexOf("ipad") != -1 || val.indexOf("iphone") != -1 || val.indexOf("ipod") != -1 || val.indexOf("webos") != -1))
						doResizeNow=false;*/
					current_obj.windowWidth=$(window).width();
				}

				if (options.responsive && doResizeNow) {
					 if(TO !== false)
						clearTimeout(TO);
					 
					
					 TO = setTimeout(function(){ doResize() }, 300); //300 is time in miliseconds
				}
			});						
			

			
			if (options.responsive) {
				doResize();
			}			
			
			

		});
	};


	//
	// plugin customization variables
	//
	$.fn.audio2_html5.defaults = {
		    playerWidth:500,
			skin: 'whiteControllers',
			initialVolume:0.5,
			autoPlay:false,
			loop:true,
			shuffle:false,	
			playerPadding: 0, //removed
			playerBg: '#000000',
			bufferEmptyColor: '#929292',
			bufferFullColor: '#454545',
			seekbarColor: '#ffffff',
			volumeOffColor: '#454545',
			volumeOnColor: '#ffffff',
			timerColor: '#ffffff',
			songTitleColor: '#a6a6a6',
			songAuthorColor: '#ffffff',
			
			bordersDivColor: '#333333',
			
			showRewindBut:true,
			showShuffleBut:true,
			showDownloadBut:true,
			showFacebookBut:true,
			facebookAppID:'845881738798857',
			facebookShareTitle:'HTML5 Audio Player PRO',
			facebookShareDescription:'A top-notch responsive HTML5 Audio Player compatible with all major browsers and mobile devices.',
			showTwitterBut:true,
			showAuthor:true,
			showTitle:true,
			showPlaylistBut:true,
			showPlaylist:true,
			showPlaylistOnInit:true,

			playlistTopPos:2,
			playlistBgColor:'#000000',
			playlistRecordBgOffColor:'#000000',
			playlistRecordBgOnColor:'#333333',
			playlistRecordBottomBorderOffColor:'#333333',
			playlistRecordBottomBorderOnColor:'#4d4d4d',
			playlistRecordTextOffColor:'#777777',
			playlistRecordTextOnColor:'#FFFFFF',
			
			categoryRecordBgOffColor:'#191919',
			categoryRecordBgOnColor:'#252525',
			categoryRecordBottomBorderOffColor:'#2f2f2f',
			categoryRecordBottomBorderOnColor:'#2f2f2f',
			categoryRecordTextOffColor:'#4c4c4c',
			categoryRecordTextOnColor:'#00b4f9',
			
			numberOfThumbsPerScreen:7,	
			playlistPadding:18,
			
			showCategories:true,
			firstCateg:'',
			selectedCategBg: '#333333',
			selectedCategOffColor: '#FFFFFF',
			selectedCategOnColor: '#00b4f9',
			selectedCategMarginBottom:12,
			
			showSearchArea:true,
			searchAreaBg: '#333333',
			searchInputText:'search...',
			searchInputBg:'#ffffff',
			searchInputBorderColor:'#333333',
			searchInputTextColor:'#333333',
			
			
			googleTrakingOn:false,
			googleTrakingCode:'',			
			
			responsive:false,
			origWidth:0,
			
			pathToDownloadFile:'',

			
			showPlaylistNumber:true,
			isSliderInitialized:false,
			isProgressInitialized:false,
			isPlaylistSliderInitialized:false

	};

})(jQuery);