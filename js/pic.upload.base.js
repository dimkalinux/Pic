// class for upload process
PIC.upload = {};

PIC.upload.base = function () {
	// FORM ELEMENTS
	var form = $("form[name='upload']"),
		submit = $(form).find("input[type='submit']"),
		input_file = $(form).find("input[type='file']"),
		status = $('#upload_status');


	var active = false;


	//
	function ui_advanced_block_init() {
		// ADVANCED BLOCK INIT
		$('#advanced_link').click(function () {
			var $ao = $('#advanced_options');

			if ($ao.is(':visible')) {
				$('#reduce_original').val('');
				$ao.fadeTo(200, 0.01, function () {
					$ao.css('opacity', 0).slideUp(200);
					$('body').focus();
				});
			} else {
				$ao.css('opacity', 0);
				$ao.slideDown(200, function () {
					$ao.fadeTo(200, 1.0);
					$('#reduce_original:visible').focus();
				});
			}

			return false;
		}).addClass("as_js_link");
	}


	//
	function ui_upload_form_check() {
		$(status).html('&nbsp;').removeClass('error');

		if ($(form).find("input[type='file'][value!='']").size() != 0) {
			$(submit).removeAttr("disabled");
		} else {
			$(submit).attr("disabled", "disabled");
		}
	}


	//
	function form_check_init() {
		$(input_file).bind("change keyup", ui_upload_form_check);
		ui_upload_form_check();
	}

	//
	function confirmExit() {
		if (active) {
			return 'Прервать загрузку?';
		}
	}

	//public
	return {
		init: function () {
			if (typeof FormData === 'function') {
				//
				PIC.upload.formdata.init();

				// DRAG N DROP INIT
				PIC.upload.dnd_formdata.init();
			} else {
				//
				PIC.upload.ajax.init();
			}

			//
			ui_advanced_block_init();

			// FORM CHECKER
			form_check_init();

			// END
			$('#uploadFile').focus();
		},

		get_form_el: function () {
			return form;
		},

		get_status_el: function () {
			return status;
		},

		get_submit_el: function () {
			return submit;
		},

		get_input_file_el: function () {
			return input_file;
		},

		get_status: function () {
			return active;
		},

		set_status: function (act) {
			active = act;
		},

		// FINISH UPLOAD PROCESS
		finish: function (url) {
			active = false;

			$(document).oneTime(300, 'finish', function () {
				$(status).html('Ok. <a href="'+url+'">Переходим к загруженной картинке</a>')
			});

			AMI.utils.makeGETRequest(url);
		},

		//
		error: function (msg) {
			active = false;
			$(status).html('');
			alert('Во время загрузки файла произошла ошибка.\nТекст ошибки: "'+msg+'"\n\nПопробуйте загрузить файл ещё раз.')

			// ACTIVATE FORM
			ui_upload_form_check();
		},

		init_unload: function () {
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

		// HIDE ADVANCED OPTIONS
		hide_advanced_options: function () {
			var ao = $('#advanced_options');

			if ($(ao).is(':visible')) {
				$(ao).fadeTo(150, 0.01, function () {
					$(ao).css('opacity', 0).delay(50).slideUp(150);
				});
			}
		},

		update_progress: function (uploaded, total) {
			var percents = parseInt(Math.floor(((uploaded / total) * 100)), 10),
				str = '';

			if (isNaN(percents)) {
				percents = 0;
			}


			if (!isNaN(uploaded) && !isNaN(total)) {
				str = AMI.utils.format_filesize(uploaded) +'&nbsp;из&nbsp;' +AMI.utils.format_filesize(total);
			}

			$('#progress_str').html(str).attr('title', 'Загружено '+percents+'%');

			$("#progressbar").progressbar({
				value: percents
			});
		},


		html5_ajax: function (ajax_settings, formData) {
			ajax_settings.processData = false;

			// Prevent jQuery from overwrite automatically generated xhr content-Type header
			// by unsetting the default contentType and inject data only right before xhr.send()
			ajax_settings.contentType = null;
			ajax_settings.__beforeSend = ajax_settings.beforeSend;
			ajax_settings.beforeSend = function (xhr, s) {
				s.data = formData;
				if (s.__beforeSend) {
					return s.__beforeSend.call(this, xhr, s);
				}
			}

			// SEND
			return $.ajax(ajax_settings);
		}
	};
}();

