/**
 * AskAnina in Nextcloud: draws Nextcloud's left pane from the entries the app in the frame sends
 * (translated, with icons and the active page), and opens a chosen entry in the frame. Messages
 * are only taken from the frame itself, on this origin. See nextcloud/README.md.
 */
(function () {
	'use strict'

	function ready(callback) {
		if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', callback)
		else callback()
	}

	// The breathing logo (owner 2026-11-11): the app's last light/dark choice (same origin as the frame, which keeps
	// it in askanina.theme), and gone once the frame has loaded; its own boot screen carries on in the same place.
	function loading() {
		var loader = document.getElementById('askanina-loading')
		var frame = document.getElementById('askanina-frame')
		if (!loader) return
		try {
			var theme = window.localStorage.getItem('askanina.theme')
			if (theme === 'light' || theme === 'dark') loader.classList.add('askanina-loading--' + theme)
		} catch (error) {
			// Storage blocked: the device's light or dark mode decides.
		}
		function done() { loader.classList.add('askanina-loading--done') }
		if (!frame) return done()
		frame.addEventListener('load', done)
		// Loaded already before this script ran (same origin, so readable).
		try {
			var page = frame.contentDocument
			if (page && page.readyState === 'complete' && page.location.href !== 'about:blank') done()
		} catch (error) {
			// Not readable yet: the load event follows.
		}
		// A frame that never says it loaded (blocked, offline) does not keep the logo for ever behind it.
		setTimeout(done, 20000)
	}

	ready(loading)

	ready(function () {
		var frame = document.getElementById('askanina-frame')
		var pane = document.getElementById('app-navigation')
		var list = document.getElementById('askanina-nav-list')
		var settingsArea = document.getElementById('app-settings')
		var settingsButton = document.getElementById('askanina-nav-settings')
		if (!frame || !pane || !list) return

		var settingsHref = null

		function open(href) {
			if (!href || !frame.contentWindow) return
			frame.contentWindow.location.hash = href.charAt(0) === '#' ? href.slice(1) : href
			document.body.classList.remove('snapjs-left')
		}

		function icon(path) {
			var image = document.createElement('img')
			var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#000" d="' + String(path).replace(/[^0-9A-Za-z .,\-]/g, '') + '"/></svg>'
			image.src = 'data:image/svg+xml,' + encodeURIComponent(svg)
			image.alt = ''
			return image
		}

		function draw(message) {
			var items = Array.isArray(message.items) ? message.items : []
			pane.classList.toggle('askanina-nav--empty', items.length === 0)
			pane.setAttribute('aria-label', message.label || 'AskAnina')
			list.textContent = ''
			var firstPinned = true
			items.forEach(function (item) {
				var entry = document.createElement('li')
				if (item.active) entry.classList.add('active')
				if (item.pinned) {
					entry.classList.add('pinned')
					if (firstPinned) entry.classList.add('first-pinned')
					firstPinned = false
				}
				var link = document.createElement('a')
				link.href = frame.getAttribute('src') + item.href
				if (item.active) link.setAttribute('aria-current', 'page')
				link.appendChild(icon(item.icon))
				link.appendChild(document.createTextNode(item.label))
				link.addEventListener('click', function (event) {
					event.preventDefault()
					open(item.href)
				})
				entry.appendChild(link)
				list.appendChild(entry)
			})
			settingsHref = message.settings ? message.settings.href : null
			settingsArea.classList.toggle('askanina-nav--empty', !message.settings)
			if (message.settings) settingsButton.textContent = message.settings.label
		}

		settingsButton.addEventListener('click', function () { open(settingsHref) })

		window.addEventListener('message', function (event) {
			if (event.origin !== window.location.origin || event.source !== frame.contentWindow) return
			if (event.data && event.data.type === 'askanina:navigation') draw(event.data)
		})

		// The app may have sent its entries before this script ran: ask again.
		function askAgain() {
			if (frame.contentWindow) frame.contentWindow.postMessage({ type: 'askanina:navigation-ready' }, window.location.origin)
		}
		frame.addEventListener('load', askAgain)
		askAgain()
	})
})()
