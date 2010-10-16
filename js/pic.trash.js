PIC.trash = function () {
	var $trash = $("#trash_block"),
		$trash_status = $("#trash_status");

	function check_num_img() {
		// check num files
		if ($(".myfiles img").length < 1) {
			// show message
			$("#no_files_message").show(300);
			// hide trash
			$trash.hide(400);
		} else {
			// show message
			$("#no_files_message").hide(300);
			// hide trash
			$trash.show(400);
		}
	}

	function deleteImage(item) {
		var delete_url = $(item).attr("rel")+'async/'
			img = $("#"+$(item).attr("id"));

		// for check status
		var timer_reached = false,
			response_received = false,
			response = '';

		// disable trash
		$trash.droppable( "option", "disabled", true );

		function check_status_end() {
			if (timer_reached && response_received) {
				// enable trash
				$trash.droppable("option", "disabled", false);

				if (response && parseInt(response.error, 10) === 0) {
					//
					$trash_status.html("файл удалён").delay(800).fadeOut(300);
					//
					$(img).parent().fadeOut(400, function () {
						$(this).remove();

						// check num imgs
						setTimeout(function () {
							check_num_img();
						}, 1000);
					});
				} else {
					$(img).fadeTo(100, 1.0);

					$trash_status.html("Ошибка");

					if (response.message) {
						alert('Ошибка: '+response.message);
					} else {
						alert('Ошибка: не удалось удалить этот файл.');
					}

					$trash_status.html("Корзина");
				}
			}
		}

		// устраиваем гонку между таймером и запросом
		setTimeout(function () {
			timer_reached = true;
			check_status_end();
		}, 1000);


		$.ajax({
			type: 	'GET',
			url: 	delete_url,
			dataType: 'json',
			beforeSend: function() {
				// disable trash
				$trash.droppable("option", "disabled", true);
				//
				$trash_status.html("удаляю&hellip;").fadeIn(100);
				//
				$(img).fadeTo(100, 0.3);
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
	}


	return {
		init: function () {
			if ($(".myfiles img").length < 1) {
				return;
			}

			// DRAG
			$(".myfiles img").draggable({
				opacity: 0.9,
				cancel: "a.ui-icon", // clicking an icon won't initiate dragging
				revert: "invalid", // when not dropped, the item will revert back to its initial position
				containment: "document", // stick to demo-frame if present
				helper: "clone",
				cursor: "move"
			});

			// DROP
			$trash.droppable({
				activeClass: "dropactive",
				hoverClass: 'drophover',
				drop: function (event, ui) {
					deleteImage(ui.draggable);
				}
			});

			$trash_status.html("Корзина");

			$trash.show(400);
		}
	};
}();

