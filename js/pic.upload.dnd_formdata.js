// class for upload process
PIC.upload.dnd_formdata = function () {
	// private
	var form = PIC.upload.base.get_form_el(),
		submit = PIC.upload.base.get_submit_el(),
		status = PIC.upload.base.get_status_el(),
		__input = PIC.upload.base.get_input_file_el(),
		__xhr = null,
		__forced_abort = false,
		__dropzone = null;


	// START UPLOADING PROCESS
	function start_upload(files) {
		var total_files = files.length,
			cur_file = 0;

		if (PIC.upload.base.get_status()) {
			AMI.log.debug('uploading active');
			return;
		}

		AMI.log.debug('files: '+total_files);

		if (total_files < 1) {
			AMI.log.debug('0 files');
			return;
		}

		var formData = new FormData();

		// APPEND FILES
		while (cur_file < total_files) {
			var current_file = files[cur_file];
			AMI.log.debug('file: '+current_file.name);

			formData.append("upload", current_file);
			cur_file++;
		}

		formData.append("async", 1);

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

				// HIDE FORM
				$("#footer,#form_upload").fadeTo(300, 0.01);
				$(status).addClass("dnd_upload");

				//
				xhr.upload.addEventListener("progress", on_progress, false);
				xhr.addEventListener("abort", on_abort, false);

				PIC.upload.base.set_status(true); 	// set start

				$(submit).attr("disabled", "disabled");

				// HIDE ADVANCED OPTIONS
				PIC.upload.base.hide_advanced_options();

				// WAIT MESSAGE
				if (total_files > 1) {
					var wait_message = 'Ожидайте, файлы загружаются на сервер&hellip;';
				} else {
					var wait_message = 'Ожидайте, файл загружается на сервер&hellip;';
				}

				// SET STATUS
				$(status)
					.removeClass('error')
					.html('<div>'+wait_message+'<a href="/" title="Прервать загрузку" id="link_abort_upload">отменить</a></div><div><span id="progress_str"></span></div>')
					.fadeTo(250, 1.0);

				$('#link_abort_upload').addClass('as_js_link');

				// UNLOAD HANDLER
				PIC.upload.base.init_unload();
			},

			error: function (xhr, textStatus, errorThrown) {
				if (!__forced_abort) {
					PIC.upload.base.error('Произошел сбой при загрузке: '+textStatus);
				}

				// SHOW FORM
				$("#footer,#form_upload").fadeTo(500, 1.0);
				$(status).removeClass("dnd_upload");

				// FORM check
				$(__input).trigger('change');
			}
  		};

  		// SEND
		__xhr = PIC.upload.base.html5_ajax(ajax_settings, formData);
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

	function dragenter(e) {
    	//__dropzone.setAttribute("dragenter", true);
	}

	function dragleave(e) {
    	//__dropzone.removeAttribute("dragenter");
	}

	/**
	 * Filters file list and leaves image files only
	 * @param {File[]} files
	 */
	function filterFileList(files) {
		var allowed_types = {png: 1, jpeg: 1, jpg: 1, gif: 1},
			result = [];

		for (var i = 0, il = files.length; i < il; i++) {
			var item = (typeof(files[i]) == 'string') ? files[i] : files[i].fileName;
			var m = (item || '').match(/\.(\w+)$/);
			if (m && m[1].toLowerCase() in allowed_types)
				result.push(files[i]);
		}

		return result;
	}


	function drop(e) {
		AMI.log.debug('drop');

    	var dt = e.dataTransfer
    		files = dt.files;

		e.preventDefault();

		if (dt && files) {
			files = filterFileList(files);

       		start_upload(files);
       	}
	}

	function stop_event(evt) {
		evt.stopPropagation();
		evt.preventDefault();
	}


	//public
	return {
		init: function () {
			AMI.log.debug('Init upload.dnd.formdata module');

			// SETUP EVENTS
			__dropzone = document.getElementById("upload_block");

		    window.addEventListener("dragenter", dragenter, true);
    		window.addEventListener("dragleave", dragleave, true);

    		document.body.addEventListener("dragover", stop_event, true);
    		document.body.addEventListener("drop", drop, true);



			//
			$('#link_abort_upload').live('click', abort);
		}
	};
}();

