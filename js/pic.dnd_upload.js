// class for upload process
PIC.dnd_upload = function () {
	// private
	var active = false,
		drop_target = null,
		re_ext = /\.(\w+)$/;


	//
	function stop_event(evt) {
		evt.stopPropagation();
		evt.preventDefault();
	}

	/**
	 * Filters file list and leaves image files only
	 * @param {File[]} files
	 */
	function filterFileList(files) {
		var allowed_types = {png: 1, jpeg: 1, jpg: 1, gif: 1, bmp: 1},
			result = [];

		for (var i = 0, il = files.length; i < il; i++) {
			var item = (typeof(files[i]) == 'string') ? files[i] : files[i].fileName;
			var m = (item || '').match(re_ext);
			if (m && m[1].toLowerCase() in allowed_types)
				result.push(files[i]);
		}

		return result;
	}

	/**
	 * Show loaded and encoded files
	 */
	function showFiles(file_list) {
		function run() {
			for (var i = 0; i < file_list.length; i++) {
				createFile(file_list[i].name, file_list[i].data_url, i * 0.1);
			}
		}

		if ($('body').hasClass('expanded')) {
			run();
		} else {
			expandPage(run);
		}
	}


	//
	function dndFirefox(evt) {
		stop_event(evt);

		var dt = evt.dataTransfer,
			files = dt.files;

		if (dt && files) {
			AMI.log.debug('file: '+files.length);
			files = filterFileList(files);

			var read_files = [],
				total_files = files.length,
				cur_file = 0;

			AMI.log.debug('file: '+cur_file+' '+total_files);

			if (total_files > 0) {
				var fd = new FormData();

				// APPEND FILES
				while (cur_file < total_files) {
					var current_file = files[cur_file];
					AMI.log.debug('file: '+current_file.name);

					fd.append("upload", current_file);

					cur_file++;
				}

				// APPEND HIDDEN
				fd.append("async", "1");

				var xhr = new XMLHttpRequest();
				xhr.open("POST", "/upload/");
				xhr.send(fd);
			}

			/*function readCallback(evt) {
				read_files.push({
					data_url: evt.target.result,
					name: files[cur_file].name
				});

				cur_file++;
				if (cur_file < total_files)
					readNext();
				else
					showFiles(read_files);
			};

			function readNext() {
				var reader = new FileReader();
				reader.onloadend = readCallback;
				reader.readAsBinaryString(files[cur_file]);
			}

			if (total_files) {
				readNext();
			}*/
		}
	}

	//
	function dndGeneric(evt) {


	}

	// public
	return {
		init: function () {
			drop_target = document.body;

			drop_target.addEventListener('dragenter', stop_event, false);
			drop_target.addEventListener('dragover', stop_event, false);
			drop_target.addEventListener('drop', (window.FileReader) ? dndFirefox : dndGeneric, false);
		}
	};
}();

