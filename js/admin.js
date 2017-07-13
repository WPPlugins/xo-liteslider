jQuery(function($) {
	$('#xo-liteslider-slide').each(function(i, e) {
		var wrapper = $(e);
		var count = wrapper.find('.slide').length;
		// スライド追加
		wrapper.find('.slide-header-append-button').click(function() {
			count++;
			var parent = $(this).parents('.slide');
			var clone = parent.clone(true, true).hide();
			clone.find('input, select, textarea').each( function(i, e) {
				var name = $(this).attr('name');
				if (name) {
					$(this).attr('name', name.replace(/^(xo_liteslider_slides)\[(.+?)\]/, '$1[' + count + ']'));
				}
				$(this).val("");
			});
      clone.find('img').remove();
			parent.after(clone.fadeIn('fast'));
		});
		// スライド削除
		wrapper.find('.slide-header-remove-button').click(function() {
			var slide = $(this).parents('.slide');
			slide.fadeOut('fast', function() {
				$(this).remove();
			});
		});
		// スライドソート
		wrapper.find('.slide-repeat').sortable({
			handle: '.slide-header'
		});
		// 画像を設定
		wrapper.find('.slide-image-setting').click(function(e) {
			e.preventDefault();
			var custom_uploader_file;
      var outputImage = $(this).parent().parent().find('.slide-image');
			var outputId = $(this).parent().find('.slide-image-id');
			if (custom_uploader_file) {
				custom_uploader_file.open();
				return;
			}
			custom_uploader_file = wp.media({
				title: messages.title,
				button: { text: messages.title },
				library: { type: 'image' },
				multiple: false
			});
			custom_uploader_file.on('select', function() {
				var images = custom_uploader_file.state().get('selection');
				images.each(function(file) {
					var attachment = file.toJSON();
					outputId.val(attachment.id);
          outputImage.find('img').remove();
          outputImage.prepend('<img src="' + attachment.sizes.thumbnail.url + '">');
				});
			});
			custom_uploader_file.open();
		});
		// 画像を削除
		wrapper.find('.slide-image-clear').click(function(e) {
			e.preventDefault();
      $(this).parent().find('.slide-image-id').val('');
      $(this).parent().find('img').remove();
		});
	});
});
