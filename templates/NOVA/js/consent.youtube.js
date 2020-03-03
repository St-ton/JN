document.addEventListener('consent.ready', function(e) {
	embedYoutube(e.detail);
});
document.addEventListener('consent.updated', function(e) {
	embedYoutube(e.detail);
});
function embedYoutube(detail) {
	if (detail !== null && typeof detail.youtube !== 'undefined' && detail.youtube === true) {
		var embeds = document.querySelectorAll('iframe.needs-consent.youtube');
		for (var i = 0; i < embeds.length; ++i) {
			embeds[i].src = embeds[i].dataset.src;
			embeds[i].className = 'youtube';
		}
	}
}
