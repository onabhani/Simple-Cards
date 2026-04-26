(function($){
	'use strict';
	$(function(){
		let frame;
		$('#ebc-upload-photo').on('click', function(e){
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({
				title: ebcAdmin.title,
				button: { text: ebcAdmin.button },
				multiple: false
			});
			frame.on('select', function(){
				const attachment = frame.state().get('selection').first().toJSON();
				$('#ebc_profile_photo').val(attachment.id);
				const $img = $('<img>').attr('src', attachment.url).attr('alt', '');
				$('#ebc-media-preview').empty().append($img);
			});
			frame.open();
		});

		$('#ebc-remove-photo').on('click', function(e){
			e.preventDefault();
			$('#ebc_profile_photo').val('');
			$('#ebc-media-preview').empty();
		});
	});
})(jQuery);
