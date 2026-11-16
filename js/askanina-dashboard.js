/**
 * "AskAnina Today" on Nextcloud's Dashboard (lib/Dashboard/TodayWidget.php): the widget shows the
 * app's own Today page (#/widget/today) in a small frame. The appointments are sealed end to end, so
 * only the app in the browser, with the user's keys, can show them; this script only makes the frame.
 * See nextcloud/README.md.
 */
(function () {
	'use strict'

	function register() {
		if (!window.OCA || !OCA.Dashboard || typeof OCA.Dashboard.register !== 'function') return
		OCA.Dashboard.register('askanina-today', function (element) {
			var frame = document.createElement('iframe')
			frame.src = OC.generateUrl('/apps/askanina/frame') + '#/widget/today'
			frame.title = 'AskAnina'
			frame.setAttribute('loading', 'lazy')
			frame.style.width = '100%'
			frame.style.height = '100%'
			frame.style.minHeight = '320px'
			frame.style.border = '0'
			frame.style.display = 'block'
			frame.style.background = 'transparent'
			element.textContent = ''
			element.appendChild(frame)
		})
	}

	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', register)
	else register()
})()
