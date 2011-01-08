PIC.upload.dnd_formdata = function () {
	// private
	var form = PIC.upload.base.get_form_el(),
		submit = PIC.upload.base.get_submit_el(),
		status = PIC.upload.base.get_status_el(),
		__input = PIC.upload.base.get_input_file_el(),
		__xhr = null,
		__forced_abort = false;


	// START UPLOADING PROCESS
	function start_upload(files) {
		var total_files = files.length,
			cur_file = 0;

		if (PIC.upload.base.get_status()) {
			return;
		}

		if (total_files < 1) {
			return;
		}

		var formData = new FormData();

		// APPEND FILES
		while (cur_file < total_files) {
			var current_file = files[cur_file];
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

				//
				xhr.upload.addEventListener("progress", on_progress, false);
				xhr.upload.addEventListener("load", on_loaded, false);
				xhr.addEventListener("abort", on_abort, false);

				PIC.upload.base.set_status(true); 	// set start

				$(submit).attr("disabled", "disabled").blur();

				// HIDE ADVANCED OPTIONS
				PIC.upload.base.hide_advanced_options();

				// WAIT MESSAGE
				if (total_files > 1) {
					var wait_message = 'Ожидайте, файлы загружаются на сервер&hellip;';
				} else {
					var wait_message = 'Ожидайте, файл загружается на сервер&hellip;';
				}

				// SHOW STATUS
				PIC.upload.base.status_show('<div>'+wait_message+'<a href="/" title="Прервать загрузку" id="link_abort_upload">отменить</a></div><div id="progress"><div id="progressbar"></div><span id="progress_str"></span></div>');

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

				// HIDE OVERLAY
				PIC.upload.base.status_hide('');
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

	function on_loaded(aEvt) {
		PIC.upload.base.status_show('Файлы обрабатываются, подождите&hellip;');
	}

	//
	function on_abort() {
		PIC.upload.base.set_status(false);
		$(status).fadeTo(150, 0.1, function () {
			PIC.upload.base.status_hide('');
		});
	}

	function abort() {
		if (__xhr !== null) {
			PIC.upload.base.status_show('Закачка останавливается&hellip;');
			__forced_abort = true;
			__xhr.abort();
		}

		return false;
	}

	function dragenter(e) {
    	stop_event(e);
	}

	function dragleave(e) {
    	stop_event(e);
	}

	/**
	 * Filters file list and leaves image files only
	 * @param {File[]} files
	 */
	function filterFileList(files) {
		var allowed_types = {png: 1, jpeg: 1, jpg: 1, gif: 1, bmp: 1, tiff: 1, tif: 1},
			result = [];

		for (var i = 0, il = files.length; i < il; i++) {
			var item = (typeof(files[i]) == 'string') ? files[i] : files[i].fileName;
			var m = (item || '').match(/\.(\w+)$/);
			if (m && m[1].toLowerCase() in allowed_types) {
				result.push(files[i]);
			}
		}

		return result;
	}


	function drop(e) {
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
			// SETUP EVENTS
		    window.addEventListener("dragenter", dragenter, false);
    		window.addEventListener("dragleave", dragleave, false);

    		document.body.addEventListener("dragover", stop_event, true);
    		document.body.addEventListener("drop", drop, true);

			//
			$('#link_abort_upload').live('click', abort);
		}
	};
}();

