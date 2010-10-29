PIC.slideshow = function () {
	var images_url = [],
		images_cached = [],
		current_id = 0,
		status_paused = false,
		speed = 10000;

	var $control = $('#control');


	function start() {
		show_next();
	}

	function pause() {
		status_paused = true;
		$control.attr('class', 'play');
		$(document).stopTime('show_next');
	}

	function play() {
		status_paused = false;
		$control.attr('class', 'pause');
		start();
	}


	function show_next() {
		AMI.log.debug('show next: '+current_id+" "+images_cached[current_id]);

		$(document).stopTime('show_next');

		if (images_cached[current_id] == undefined) {
			current_id = 0;
		}

		var image = images_cached[current_id],
			image_id = $(image).attr('thumbs_id');

		current_id++;

		$(image).fadeIn(900);

		//
		if ($('#slideshow_block img').length > 0) {
			$('#slideshow_block img').fadeOut(400, function () {
				$(this).remove();
				$('#slideshow_block').append(image).center();

				// RUN timer if not PAUSED
				if (!status_paused) {
					$(document).oneTime(speed, 'show_next', show_next);
				}
			});
		} else {
			$('#slideshow_block').append(image).center();

			// RUN timer if not PAUSED
			if (!status_paused) {
				$(document).oneTime(speed, 'show_next', show_next);
			}
		}

		// set gallery image active class
		$('#thumbs_block').find('img').removeClass('active');
		$('img[thumbs_id='+image_id+']').addClass('active');

		// FOR .live
		return false;
	}


	function select_img(image_id) {
		AMI.log.debug('select: '+current_id);

		// STOP slideshow
		pause();

		var image = $(images_cached).filter('[thumbs_id='+image_id+']');

		_(images_cached).each(function (img, index) {
			if (parseInt($(img).attr('thumbs_id'), 10) === image_id) {
				current_id = ++index;
				_.breakLoop();
			}
		});

		$(image).fadeIn(300);

		if ($('#slideshow_block img').length > 0) {
			$('#slideshow_block img').fadeOut(100, function () {
				$(this).remove();
				$('#slideshow_block').append(image).center();
			});
		} else {
			$('#slideshow_block').append(image).center();
		}

		$('#thumbs_block').find('img').removeClass('active');
		$('img[thumbs_id='+image_id+']').addClass('active');

		AMI.log.debug('after select: '+current_id);
	}

	return {
		init: function () {
			var timer_reached = false,
				all_images_preloaded = false;

			$('#slideshow_block').append('<div id="loader" class="span-12">Загружаю слайдшоу&hellip;</div>').center();

			function check_status_loaded_end() {
				if (timer_reached && all_images_preloaded) {
					$('#loader').fadeOut(500, function () {
						$(this).remove();

						$('#thumbs_block').fadeIn(800, function () {
							$(document).oneTime(300, start);
						});
					});
				}
			}

			// устраиваем гонку между таймером и запросом
			setTimeout(function () {
				timer_reached = true;
				check_status_loaded_end();
			}, 1500);

			$('span.images').each(function () {
				var cacheImage = document.createElement('img');
				$(cacheImage)
					.attr('src', $(this).attr('src'))
					.attr('thumbs_id', $(this).attr('thumbs_id'))
					.addClass('fancy_image_dark');

				images_cached.push(cacheImage);
				$(this).remove();

				all_images_preloaded = true;
				check_status_loaded_end();
			});


			// bind click to large image
			$('#slideshow_block img', $('#slideshow_block')[0]).live('click', show_next);

			// setup initial control state
			$control.attr('class', 'pause');

			// setup gallery image events
			$('#thumbs_block img').click(function () {
				select_img(parseInt($(this).attr('thumbs_id'), 10));
			});

			// setup control events
			$('#control').click(function () {
				if (status_paused) {
					play();
				} else {
					pause();
				}
			});
		}
	};
}();

