PIC.ajaxify = function () {
	return {

		shorten_link: function () {
			if (!$('a#link_shortener').attr('key-id')) {
				return;
			}

			if (!$('a#link_shortener').attr('key-delete')) {
				return;
			}

			$('a#link_shortener').click(function () {
				if ($(this).hasClass('active')) {
					return false;
				} else {
					$(this).addClass('active');
				}

				//
				var a_url = $(this).attr('href'),
					elem = $(this),
					a_text = elem.text();


				// for check status
				var timer_reached = false,
					response_received = false,
					response = '';


				//
				function check_status_end() {
					if (timer_reached && response_received) {
						// останавливаем индикатор
						elem.removeClass('active');

						if (response && parseInt(response.result, 10) === 1) {
							$('#show').val(response.message);

							// update share links
							$('#share_menu a').each(function () {
								$(this).attr('href', $(this).attr('href').split('=')[0] + '=' + encodeURIComponent(response.message));
							})

							//
							elem.text('готово').delay(800).fadeOut(300);
						} else {
							// error here
							elem.text(a_text).addClass('as_js_link').removeClass('loading');
							if (response && response.message) {
								alert('Ошибка: '+response.message);
							} else {
								alert('Ошибка: не удалось укоротить ссылку');
							}
						}
					}
				}


				// устраиваем гонку между таймером и запросом
				setTimeout(function() {
					timer_reached = true;
					check_status_end();
				}, 1000);


				$.ajax({
					type: 	'POST',
					url: 	a_url,
					data:	{ t_action: '1', t_key_id: elem.attr('key-id'), t_key_delete: elem.attr('key-delete') },
					dataType: 'json',

					beforeSend: function() {
						elem.text('ожидайте...').removeClass('as_js_link').addClass('loading');
					},

					error: function () {
						response_received = true;
						check_status_end();
					},

					success: function (r) {
						response_received = true;
						response = r || '';
						check_status_end();
					}
				});
				return false;
			}).addClass("as_js_link").show();
		},


		delete_image: function () {
			$('a#delete_image').click(function () {
				if ($(this).hasClass('active')) {
					return false;
				} else {
					$(this).addClass('active');
				}

				var a_url = $(this).attr('href')+'async/',
					icon = $(this).find('span.icon');


				$.ajax({
					type: 	'GET',
					url: 	a_url,
					dataType: 'json',
					beforeSend: function() {
						$(document).oneTime(300, 'icon_loading', function () {
							$(icon).addClass('loading');
						});
					},
					complete: function() {
						$(document).stopTime('icon_loading');
						$(icon).removeClass('loading');
						$('a#delete_image').removeClass('active');
					},
					error: function () {
						alert('Ошибка: не удалось удалить этот файл.');
					},
					success: function (r) {
						if (r && parseInt(r.error, 10) === 0) {
								$('.container').fadeOut(250, function() {
									$('body').attr('id', 'message_page');

									$('#links_wrap, #links_block').remove();

									$('#main_block').removeClass('span-3').addClass('span-15 prepend-5 last block_body');
									$('#main_block').html('<h2>Файл удалён</h2><p>Файл успешно удалён с сервера.<br/><br/><a href="/">Перейти на главную страницу</a></p>');
									$(document).attr("title", "Файл удалён");
								}).fadeIn(200);
						} else {
							//
							$(icon).removeClass('loading');
							$('a#delete_image').removeClass('active');

							//
							if (r.message) {
								alert('Ошибка: '+r.message);
							} else {
								alert('Ошибка: не удалось удалить этот файл.');
							}
						}
					}
				});
				return false;
			}).addClass("as_js_link").attr('disabled', 'disabled');
		},

		delete_group_image: function () {
			$('a#delete_image').click(function () {
				if ($(this).hasClass('active')) {
					return false;
				} else {
					$(this).addClass('active');
				}

				var a_url = $(this).attr('href')+'async/',
					icon = $(this).find('span.icon');

				$.ajax({
					type: 	'GET',
					url: 	a_url,
					dataType: 'json',
					beforeSend: function() {
						$(document).oneTime(300, 'icon_loading', function () {
							$(icon).addClass('loading');
						});
					},
					complete: function() {
						$(document).stopTime('icon_loading');
						$(icon).removeClass('loading');
						$('a#delete_image').removeClass('active');
					},
					error: function () {
						alert('Ошибка: не удалось удалить эту группу файл.');
					},
					success: function (r) {
						if (r && parseInt(r.error, 10) === 0) {
							$('.container').fadeOut(250, function() {
								$('body').attr('id', 'message_page');

								$('#links_group_block').remove();

								$('#main_block')
									.removeClass('span-3 prepend-1')
									.addClass('span-15 prepend-5 last block_body')
									.html('<h2>Файлы удалёны</h2><p>Файлы успешно удалёны с сервера.<br/><br/><a href="/">Перейти на главную страницу</a></p>');
									$(document).attr("title", "Файлы удалёны");
							}).fadeIn(200);
						} else {
							//
							$(icon).removeClass('loading');
							$('a#delete_image').removeClass('active');

							if (r.message) {
								alert('Ошибка: '+r.message);
							} else {
								alert('Ошибка: не удалось удалить группу файлов.');
							}
						}
					}
				});
				return false;
			}).addClass("as_js_link").attr('disabled', 'disabled');
		},


		gallery_change_image: function () {
			$('#gallery_block a').click(function () {
				if ($(this).hasClass('active')) {
					return false;
				} else {
					$(this).addClass('active');
				}

				var g_image_link = $(this),
					g_image_img = $(g_image_link).find('img'),
					image_info = $(g_image_link).attr('rel'),
					new_img_url = image_info.split('*')[0],
					img_original_url = image_info.split('*')[1],
					img = $('#img_block').find('img'),
					link = $('#img_block').find('a');


					$(img).animate({opacity: 0.1}, 75, '', function () {
						var t_img = new Image();
						t_img.onload = function () {
							$(img)
								.attr('src', new_img_url)
								.attr('alt', $(g_image_img).attr('alt'));

							$(img).animate({opacity: 1}, 150, '');
							$(t_img).remove();
						};
						$(t_img).attr('src', new_img_url);
						$(link).attr('href', img_original_url);
						$('#header_original_link').attr('href', img_original_url);
						$('#header_original_link').html(image_info.split('*')[2]+'&#8202;x&#8202;'+image_info.split('*')[3]+'&nbsp;'+PIC.utils.format_filesize(image_info.split('*')[4]));
						// set page link
						$(document).attr("title", $(g_image_img).attr('alt'));
					});

					// remove link active class
					$(this).removeClass('active');

					// set gallery image active class
					$('#gallery_block').find('img').removeClass('active');
					$(this).find('img').addClass('active');


				return false;
			}).addClass("as_js_link").attr('disabled', 'disabled');
		}
	};
}();
