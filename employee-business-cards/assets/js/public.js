(function(){
	'use strict';

	document.addEventListener('click', function(event){
		const shareBtn = event.target.closest('.ebc-share-btn');
		if (shareBtn) {
			const shareUrl = shareBtn.dataset.ebcShareUrl || '';
			const sharePrompt = shareBtn.dataset.ebcSharePrompt || '';
			if (!shareUrl) {
				return;
			}
			if (navigator.share) {
				navigator.share({ title: document.title, url: shareUrl }).catch(function(){});
				return;
			}
			window.prompt(sharePrompt, shareUrl);
			return;
		}

		const qrBtn = event.target.closest('.ebc-qr-toggle');
		if (qrBtn) {
			const targetId = qrBtn.getAttribute('aria-controls');
			if (!targetId) {
				return;
			}
			const target = document.getElementById(targetId);
			if (!target) {
				return;
			}
			const expanded = qrBtn.getAttribute('aria-expanded') === 'true';
			qrBtn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
			if (expanded) {
				target.setAttribute('hidden', '');
			} else {
				target.removeAttribute('hidden');
				target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
			}
			const label = qrBtn.querySelector('span');
			if (label) {
				const showLabel = label.dataset.showLabel || label.textContent;
				const hideLabel = label.dataset.hideLabel || label.textContent;
				label.textContent = expanded ? showLabel : hideLabel;
			}
		}
	});
})();
