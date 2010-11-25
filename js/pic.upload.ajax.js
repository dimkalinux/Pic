// class for upload process
PIC.upload.ajax = function () {
	// private
	var form = PIC.upload.base.get_form_el(),
		submit = PIC.upload.base.get_submit_el(),
		status = PIC.upload.base.get_status_el();


	// START UPLOADING PROCESS
	function start() {
		PIC.upload.base.set_status(true); 	// set start

		$(submit).attr("disabled", "disabled");

		// HIDE ADVANCED OPTIONS
		PIC.upload.base.hide_advanced_options();

		// SET STATUS
		$(status)
			.removeClass('error')
			.html('Ожидайте, файл загружается на сервер&hellip; <a href="/" id="link_abort_upload">отменить</a>')
			.fadeIn(350);

		// UNLOAD HANDLER
		PIC.upload.base.init_unload();
	}

	// PUBLIC INTERFACE
	return {
		//
		init: function () {
			AMI.log.debug('Init upload.ajax module');

			// form
			var options = {
				dataType: 'json',
				resetForm: true,
				cleanForm: true,
				type: 'post',
				data: { async: 1 },
				success: function (r) {
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
				error: function (r) {
					PIC.upload.base.error("Произошел сбой при загрузке");
				},
			};

			// INIT JQUERY.FORMS
			$(form).bind("submit", function () {
				$(this).ajaxSubmit(options);
				start();
				return false;
			});
		}
	};
}();

