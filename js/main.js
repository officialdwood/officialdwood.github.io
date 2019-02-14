var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);


var wpOffset = 60;


var player;
var layout =  {
	doneAnimateSkill : false,
	init : function() {
		this.initParallax();
		this.initStickyMenu();
		this.initClickScrollTo();
		this.initPortfolioGallery();
		this.initEmphasizeSec();
		this.initTestimonial();
		this.initMap();
		this.initVideo();
		this.initHead();
	},
	initHead : function() {
		var $img 	= $('header .big-header:visible');
		$('header').height($(window).height());
		// $('#overlay').height($(window).height());
		$(window).resize(function() {
			$img 	= $('header .big-header:visible');

			if ($img[0] == undefined)
				return; 

			var nW = $img[0].naturalWidth;
			var nH = $img[0].naturalHeight;

			var css_obj = {};
			var dim 	= '';

			if (nW > $(window).width() && nH <= $(window).height()) {
				dim = 'height';
			} else if (nH > $(window).height() && nW <= $(window).width()) {
				dim = 'width';
			} else {
				var w_diff		= $(window).width() - nW;
				var h_diff 		= $(window).height() - nH;
				var n_height 	= nH * $(window).width() / nW;

				//compute by width
				if (n_height > $(window).height())
					dim = 'width';
				else
					dim = 'height';
			}

			if (dim == 'width') {
				var n_height = nH * $(window).width() / nW;
				css_obj = {
					'width'		: $(window).width(),
					'top'	: $(window).height() - n_height
				}
			} else {
				var n_width = $(window).height() * nW / nH;
				css_obj = {
					'height'	: $(window).height(),
					'left'		: ($(window).width() - n_width)/2
				}
			}

			$('header').height($(window).height());
			$img.removeAttr('style');
			$img.css(css_obj);
			//alert($img.height());
		});
		imagesLoaded($img, function(){
			$('#overlay').fadeOut();
			setTimeout(function(){
				$(window).trigger('resize');
			},500)
		});

		$('.head .hint a').click(function(e){
			e.preventDefault();
			$('#menu').ScrollTo({
				duration: 1000,
			});
		});
	},
	initVideo : function() {
		$('#myModal').on('hidden.bs.modal', function (e) {
			player.stopVideo();
		});
		$('#myModal').on('shown.bs.modal', function (e) {
			resizeVideoModal($('#myModal'));
		});
		$(window).resize(function(){
			resizeVideoModal($('#myModal'));
		});
	},
	initMap : function() {
		var latVal = parseFloat($('#map').attr('data-lat'));	
		var lngVal = parseFloat($('#map').attr('data-lng'));
            var latlng = {lat : latVal, lng : lngVal};
	  
	    // Map Options
	    var myOptions = {
	            zoom: 15,
	            center: latlng,
	            mapTypeId: google.maps.MapTypeId.ROADMAP,
	            disableDefaultUI: true,
	            scrollwheel: false,
	    };

	    var map = new google.maps.Map(document.getElementById('map_content'), myOptions);
	    var markerSecond = new google.maps.Marker({
	        position: latlng,
	        map: map,
	    });

	    $(window).resize(function(){
			map.setCenter(markerSecond.getPosition());  
	    });
	    $('.map .backdrop').click(function(e){
	    	$('.map').addClass('show');
	    	setTimeout(function(){
	    		google.maps.event.trigger(map, "resize");
	    		map.setCenter(markerSecond.getPosition());  
	    	},600)
	    });
	},
	initTestimonial : function() {
		$('.testimonial .flexslider').flexslider({
			 prevText: "",           //String: Set the text for the "previous" directionNav item
			 nextText: ""
			// animation: "slide" 
		});	
	},
	initEmphasizeSec : function() {
		$(window).scroll(function(){
			if (isScrolInsideSection($('#achievement'), getScrollOffset())) {
				if ($('#achievement').data('first-time') != undefined)
					return;
				$('#achievement').data('first-time', 1);
				$('#achievement .stat').each(function(){
					$(this).animateNumber({ number:  parseInt($(this).html())});
				});
			}

		})
	},
	initParallax : function() {
		if (isMobileDevice())
			return;
		
		$('.parallax').addClass('active');
	},
	initStickyMenu: function() {
		//Sticky menu
	    
		$(document).scroll(function() {
			var $menu 		= $('#menu');
		    var $header 	= $('header');
			var origOffsetY = $menu.offset().top;


			if ($(window).scrollTop() >= origOffsetY) {
				$menu.find('nav').addClass('navbar-fixed-top');
			} else {
				$menu.find('nav').removeClass('navbar-fixed-top');
			}  

		});
		$(document).trigger('scroll');
	},
	initClickScrollTo : function() {
		$('#menu nav a, .s-menu a').click(function(e){
			e.preventDefault();

			var id = $(this).attr('href');
			$('#menu-content').collapse('hide');
			var second = $(window).width() > 767 ? 0 : 500;

			setTimeout(function(){
				if (id != '#') {
					$(id).ScrollTo({
						duration: 1000,
						offsetTop: getScrollOffset()
					});
				} else {
					$('#head').ScrollTo({
						duration: 1000
					});
				}
			},second);
		});

		$(window).scroll(function(){
			var find = false
			$('#menu .navbar-right a').each(function(){
				
				var $section = $($(this).attr('href'));
				if (isScrolInsideSection($section, getScrollOffset())) {
					if (!$(this).parents('li').is($('#menu nav li.active'))) {
						$('#menu nav li').removeClass('active');
						$(this).parents('li').addClass('active');
						find = true;
					}
				}
			});
			if (!find) {
				if (isScrolInsideSection($('#head'))) {
					$('#menu nav li').removeClass('active');
				}
			}
		});
	},
	initPortfolioGallery : function() {
		$(".project a[rel^='prettyPhoto']").prettyPhoto({
			animation_speed:'normal',
			theme:'light_square',
			slideshow:3000, 
			autoplay_slideshow: false,
			social_tools : '',
			modal : false,
			markup : '<div class="pp_pic_holder"> \
						<div class="ppt">&nbsp;</div> \
						<div class="pp_top"> \
							<div class="pp_left"></div> \
							<div class="pp_middle"></div> \
							<div class="pp_right"></div> \
						</div> \
						<div class="pp_content_container"> \
							<div class="pp_left"> \
							<div class="pp_right"> \
								<div class="pp_content"> \
									<div class="pp_loaderIcon"></div> \
									<div class="pp_fade"> \
										<a href="#" class="pp_expand" title="Expand the image">Expand</a> \
										<div class="pp_hoverContainer"> \
											<a class="pp_next" href="#">next</a> \
											<a class="pp_previous" href="#">previous</a> \
										</div> \
										<div id="pp_full_res"></div> \
										<div class="pp_details"> \
											<div class="pp_nav"> \
												<div class="pp-nav-wrapper"> \
													<a href="#" class="pp_arrow_previous">Previous</a> \
													<p class="currentTextHolder">0/0</p> \
													<a href="#" class="pp_arrow_next"></a> \
												</div> \
											</div> \
											<p class="pp_description"></p> \
											<a class="pp_close" href="#">Close</a> \
										</div> \
									</div> \
								</div> \
							</div> \
							</div> \
						</div> \
						<div class="pp_bottom"> \
							<div class="pp_left"></div> \
							<div class="pp_middle"></div> \
							<div class="pp_right"></div> \
						</div> \
					</div> \
					<div class="pp_overlay"></div>', 
			changepicturecallback : function() {

				var nHeight = $('#fullResImage').height() + $('.pp_details').outerHeight() + 10;
				$('.pp_right .pp_content').height(nHeight);

				if ($(window).width() > 640)
					return;

				$('.pp_pic_holder').css({
					'width' : $(window).width() - 20,
					'left'	: '10px'
				});
				var innerWidth = $('.pp_pic_holder').width() - 40;
				$('.pp_content').width(innerWidth);
				$('.pp_details').width(innerWidth);
				$('.pp_hoverContainer').css({
					width: innerWidth,
					height: innerWidth * $('#fullResImage').height() / $('#fullResImage').width()
				});
				$('#fullResImage').css({
					width: innerWidth,
					height: innerWidth * $('#fullResImage').height() / $('#fullResImage').width()
				});
			}			
		});
	}
};
$('#head').imagesLoaded(function() {
    setTimeout(function(){
        $('#ld_back').fadeOut();
    }, 500)
});
$(document).ready(function() {	
	layout.init();	
});

function isScrolInsideSection($section, offset) {
	var offset = (offset == undefined) ? 0 : offset;
	var wPos = $(window).scrollTop();
	if (wPos >= $section.offset().top - offset && wPos <= $section.offset().top + $section.innerHeight() - offset) {
		return true;
	}
	return false;
}
function getScrollOffset() {
	return $('#menu .navbar').innerHeight();
} 
function resizeVideoModal($modal) {
	console.log($modal);
	var pWidth = $modal.find('iframe').attr('width');
	var pHeight = $modal.find('iframe').attr('height');

	var width 	= $modal.find('.modal-body').width();
	var height 	= width * pHeight / pWidth;
	$modal.find('iframe').width(width);
	$modal.find('iframe').height(height);
}
function onYouTubeIframeAPIReady() {
	player = new YT.Player('vid', {
	  events: {
	    // 'onReady': onPlayerReady,
	    // 'onStateChange': onPlayerStateChange
	  }
	});
}
function isMobileDevice() {
	return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}
