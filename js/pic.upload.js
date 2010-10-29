// class for upload process
PIC.upload = function () {
	// private
	var active = false,
		startTime = null,
		form = $("form[name='upload']"),
		submit = $(form).find("input[type='submit']");

	function confirmExit() {
		if (active === true) {
			//return UP.env.uploadWarnMsg;
		}
	}

	function formCheck() {
		$('#upload_status').html('&nbsp;').removeClass('error');

		var submit = $("input[type='file']").parent().find("input[type='submit']");
		if ($("input[type='file'][value!='']").size() != 0) {
			$(submit).removeAttr("disabled");
		} else {
			$(submit).attr("disabled", "disabled");
		}
	}


	//public
	return {
		init: function () {
			$("input[type='file']").bind("change", formCheck).bind("keyup", formCheck);
			formCheck();

			// form
			var options = {
				dataType: 'json',
				resetForm: true,
				cleanForm: true,
				type: 'post',
				data: { async: 1 },
				success: function (r) {
					if (r && parseInt(r.error, 10) === 0) {
						PIC.upload.finish(r.url);
					} else {
						if (r.message) {
							PIC.upload.error(r.message);
						} else {
							PIC.upload.error('Произошел сбой при загрузке');
						}
					}
				},
				error: function (r) {
					PIC.upload.error("Произошел сбой при загрузке");
				},
				beforeSend: function () {
					if ($('#advanced_options').is(':visible')) {
						$('#advanced_options').fadeTo(200, 0.01, function () {
							$('#advanced_options').css('opacity', 0);
							$('#advanced_options').slideUp(200);
						});
					}
					$('#upload_status').html('Ожидайте, файл загружается на сервер&hellip;').fadeIn(200);
				},
			};

			$(form).bind("submit", function () {
				$(submit).attr("disabled", "disabled");

				$(this).ajaxSubmit(options);
				PIC.upload.start();
				return false;
			});

			// at the end
			$('#uploadFile').focus();

			$('#advanced_link').click(function () {
				if ($('#advanced_options').is(':visible')) {
					$('#reduce_original').val('');
					$('#advanced_options').fadeTo(200, 0.01, function () {
						$('#advanced_options').css('opacity', 0);
						$('#advanced_options').slideUp(200);
						$('body').focus();
					});
				} else {
					$('#advanced_options').css('opacity', 0);
					$('#advanced_options').slideDown(200, function () {
						$('#advanced_options').fadeTo(200, 1.0);
						$('#reduce_original:visible').focus();
					});
				}

				return false;
			}).addClass("as_js_link");
		},

		start: function () {
			active = true; 	// set start

			$(submit).attr("disabled", "disabled");

			$('#upload_status')
				.removeClass('error')
				.html('Ожидайте, файл загружается на сервер&hellip; <a href="/" id="link_abort_upload">отменить</a>')
				.fadeIn(350);

			// set onunload event
			var root = window.addEventListener || window.attachEvent ? window : document.addEventListener ? document : null;
			if (typeof root.onbeforeunload !== "undefined") {
				root.onbeforeunload = confirmExit;
			} else {
				window.onbeforeunload = function (o) {
					if (confirmExit()) {
						o.returnValue = confirmExit();
					}
				};
			}
			return true;
		},

		//
		finish: function (url) {
			active = false;
			$(document).oneTime(300, 'finish', function () { $('#upload_status').html('Ok. Переходим на <a href="'+url+'">к загруженной картинке</a>') });;
			AMI.utils.makeGETRequest(url);
		},

		//
		error: function (msg) {
			active = false;
			$('#upload_status').html('Ошибка: '+msg).addClass('error');
		}
	};
}();

