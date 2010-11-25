// class for upload process
PIC.upload.formdata = function () {
	// private
	var form = PIC.upload.base.get_form_el(),
		submit = PIC.upload.base.get_submit_el(),
		status = PIC.upload.base.get_status_el(),
		__input = PIC.upload.base.get_input_file_el(),
		__xhr = null,
		__forced_abort = false;


	// START UPLOADING PROCESS
	function start() {
		var files = document.getElementById("file_input").files,
			total_files = files.length,
			cur_file = 0;

		AMI.log.debug('files: '+total_files);

		if (total_files > 0) {
			var formData = new FormData();

			//xhr = new XMLHttpRequest();

			// APPEND FILES
			while (cur_file < total_files) {
				var current_file = files[cur_file];
				AMI.log.debug('file: '+current_file.name);

				formData.append("upload", current_file);
				cur_file++;
			}

			formData.append("async", 1);
		}

		//
		ajax_settings = {
  			url: '/upload/',
  			type: 'POST',
  			dataType: 'json',
  			cache: false,

  			success: function (r, textStatus, xhr) {
    			if (r && parseInt(r.error, 10) === 0) {
					PIC.upload.base.finish(r.url);
				} else {
					if (r.message) {
						PIC.upload.base.error(r.message);
					} else {
						PIC.upload.base.error('Произошел сбой при загрузке');
					}
				}
  			},

  			beforeSend: function (xhr) {
				//
				__forced_abort = false;

				//
				xhr.upload.addEventListener("progress", on_progress, false);
				xhr.addEventListener("abort", on_abort, false);

				PIC.upload.base.set_status(true); 	// set start

				$(submit).attr("disabled", "disabled");

				// HIDE ADVANCED OPTIONS
				PIC.upload.base.hide_advanced_options();

				// SET STATUS
				$(status)
					.removeClass('error')
					.html('<div>Ожидайте, файл загружается на сервер&hellip;&nbsp;<a href="/" id="link_abort_upload">отменить</a></div><div><span id="progress_str"></span></div>')
					.fadeTo(350, 1.0);

				$('#link_abort_upload').addClass('as_js_link');

				// UNLOAD HANDLER
				PIC.upload.base.init_unload();
			},

			error: function (xhr, textStatus, errorThrown) {
				if (!__forced_abort) {
					PIC.upload.base.error('Произошел сбой при загрузке: '+textStatus);
				}

				// FORM check
				$(__input).trigger('change');
			}
  		};

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
		__xhr = $.ajax(ajax_settings);
	}

	//
	function on_progress(aEvt) {
		if (aEvt.lengthComputable) {
			PIC.upload.base.update_progress(aEvt.loaded, aEvt.total);
		}
	}

	//
	function on_abort() {
		PIC.upload.base.set_status(false);
		$(status).fadeTo(150, 0.1, function () {
			$(this).html('&nbsp;');
		});
	}

	function abort() {
		if (__xhr !== null) {
			$(status).html("Останавливается закачка&hellip;");
			__forced_abort = true;
			__xhr.abort();
		}

		return false;
	}


	//public
	return {
		init: function () {
			AMI.log.debug('Init upload.formdata module');

			$(form).bind("submit", function () {
				start();
				return false;
			});

			$('#link_abort_upload').live('click', abort);
		}
	};
}();

