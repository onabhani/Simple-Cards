(function(){
	'use strict';

	document.addEventListener('click', function(event){
		const button = event.target.closest('.ebc-share-btn');
		if (!button) {
			return;
		}

		const shareUrl = button.dataset.ebcShareUrl || '';
		const sharePrompt = button.dataset.ebcSharePrompt || '';

		if (!shareUrl) {
			return;
		}

		if (navigator.share) {
			navigator.share({ title: document.title, url: shareUrl }).catch(function(){});
			return;
		}

		window.prompt(sharePrompt, shareUrl);
	});
})();
