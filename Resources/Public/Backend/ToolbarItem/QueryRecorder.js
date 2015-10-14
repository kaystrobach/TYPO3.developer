window.addEventListener(
	'load',
	function() {
		var element = document.getElementById('tx-developer-query-recorder');
		element.addEventListener('click', function() {
			var cssClass = element.getAttribute('class');
			if(cssClass.indexOf('running') > -1) {
				new Ajax.Request(
					TYPO3.settings.ajaxUrls['developer::disableQueryRecording'],
					{
						onComplete: function() {
							element.setAttribute('class', 'separator');
						}
					}
				);
			} else {
				new Ajax.Request(
					TYPO3.settings.ajaxUrls['developer::enableQueryRecording'],
					{
						onComplete: function() {
							element.setAttribute('class', 'separator running');
						}
					}
				);
			}
		})
	}
);