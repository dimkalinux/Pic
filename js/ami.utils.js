AMI.utils = function () {
	return {
		JSLinkListToggle: function (t) {
			var itemShowID = t.attr("rel"),
				list = t.parent().parent();

			// hide all
			list.children("li").each(function () {
				var itemHideID = $(this).children("span.as_js_link").attr("rel");
				if (itemHideID != itemShowID) {
					$("#"+itemHideID).hide();
				}
			});

			$("#"+itemShowID).toggle();

			if ($("#"+itemShowID).is(":visible")) {
				$.cookie(UP.env.itemInfoStatusCookie, itemShowID, { expires: 24, path: '/' });
				$("[required='1'][value='']:first").focus();
			} else {
				$.cookie(UP.env.itemInfoStatusCookie, '', { expires: 24, path: '/' });
			}
		},

		makePOSTRequest: function (url, options) {
			try {
			  	var form = $('<form/>');

			  	form.attr('action', url);
			  	form.attr('method', 'post');
			  	form.appendTo('body');

				 if (options) {
	             	for (var n in options) {
		                $('<input type="hidden" name="'+n+'" value="'+options[n]+'"/>').appendTo(form);
					}
				}

				form.submit();
			} finally {
				form.remove();
			}
		},

		makeGETRequest: function (url) {
			window.location = url;
		},

		getCase: function (value, gen_pl, gen_sg, nom_sg)
		{
			if ((value % 100 >= 5) & (value % 100 <= 20)) {
				return gen_pl;
			}

			value = value % 10;
			if (((value >= 5) & (value <= 9)) | (value === 0)) {
				return gen_pl;
			}

			if ((value >= 2) & (value <= 4)) {
				return gen_sg;
			}

			if (value === 1) {
				return nom_sg;
			}
		},

		format_filesize: function (bytes, quoted) {
			var b = parseInt(bytes, 10),
				span_start = (quoted) ? '<span class=\"filesize\">' : '<span class="filesize">';

			if (b < 1024) {
			    return b+'&nbsp;'+span_start+'б</span>';
		    } else if (b < 1048576) {
				return (Math.round((b / 1024) * 10) / 10) + '&thinsp;'+span_start+'КБ</span>';
		    } else if (b < 1073741824) {
				return (Math.round((b / 1048576) * 10) / 10) + '&thinsp;'+span_start+'МБ</span>';
		    } else if (b < 1099511627776) {
				return (Math.round((b / 1073741824) * 10) / 10) + '&thinsp;'+span_start+'ГБ</span>';
		    } else {
				return (Math.round((b / 1099511627776) * 10) / 10) + '&thinsp;'+span_start+'ТБ</span>';
		    }
		},

		gct: function () {
			return new Date().getTime();
		},

		init_form: function (form) {
			if (form) {
				$(form).find("input:password[value=''], input:text[value='']").filter(':first').focus();
			}
		}
	};
}();

