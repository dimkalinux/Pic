PIC.utils = function () {
	return {
		preload_gallery_images: function () {
			var images = [],
				cached_images = [],
				preview_url = $('#img_block').find('img').attr('src');

			// make array of imgs urls
			$('#gallery_block a').each(function () {
				var g_image_link = $(this),
					image_info = $(g_image_link).attr('rel'),
					new_img_url = image_info.split('*')[0];

					if (preview_url != new_img_url) {
						images.push(new_img_url);
					}
			});

			//
			_.each(images, function (img_url) {
				var cacheImage = document.createElement('img');
				cacheImage.src = img_url;
				cached_images.push(cacheImage);
			});
		}
	};
}();

