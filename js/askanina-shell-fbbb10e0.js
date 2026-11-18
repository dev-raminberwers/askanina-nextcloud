/*
 * SPDX-FileCopyrightText: Ramin Berwers
 * SPDX-License-Identifier: Apache-2.0
 */
/**
 * The AskAnina page in Nextcloud (App Store app): the AskAnina web app runs in a frame from its own
 * address, another origin. This script only talks to that frame by postMessage, and only with the
 * web app's origin (from the app's configuration) and the frame's own window:
 *  - the frame says hello; this page answers with Nextcloud's light/dark theme (the welcome);
 *  - the frame sends its menu entries; this page draws them in Nextcloud's left pane;
 *  - a chosen entry is asked of the frame (askanina:navigate), never set from here.
 * Nothing of Nextcloud (session, password, files) is ever passed. See README.md.
 */
(function () {
	'use strict'

	var PROTOCOL = 1

	function ready(callback) {
		if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', callback)
		else callback()
	}

	/** This app's settings from Nextcloud's initial state (appOrigin). */
	function settings(key) {
		try {
			if (window.OCP && OCP.InitialState && typeof OCP.InitialState.loadState === 'function') return OCP.InitialState.loadState('askanina', key)
		} catch (error) {
			// Fall back to the element Nextcloud writes the state into.
		}
		var element = document.getElementById('initial-state-askanina-' + key)
		if (!element) return null
		try {
			return JSON.parse(atob(element.value))
		} catch (error) {
			return null
		}
	}

	/** The theme Nextcloud shows (body data-themes), or null when it follows the device. */
	function theme() {
		var body = document.body
		if (!body) return null
		var themes = String(body.getAttribute('data-themes') || '').split(',').map(function (item) { return item.trim() })
		if (themes.some(function (item) { return item.indexOf('dark') === 0 }) || body.hasAttribute('data-theme-dark')) return 'dark'
		if (themes.some(function (item) { return item.indexOf('light') === 0 }) || body.hasAttribute('data-theme-light')) return 'light'
		return null
	}

	function icon(path) {
		var image = document.createElement('img')
		var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#000" d="' + String(path).replace(/[^0-9A-Za-z .,\-]/g, '') + '"/></svg>'
		image.src = 'data:image/svg+xml,' + encodeURIComponent(svg)
		image.alt = ''
		return image
	}

	function start() {
		var state = settings('shell')
		var appOrigin = state && typeof state.appOrigin === 'string' ? state.appOrigin : ''
		var frame = document.getElementById('askanina-frame')
		var pane = document.getElementById('app-navigation')
		var list = document.getElementById('askanina-nav-list')
		var settingsArea = document.getElementById('app-settings')
		var settingsButton = document.getElementById('askanina-nav-settings')
		var loader = document.getElementById('askanina-loading')
		if (!appOrigin || !frame || !pane || !list) return

		var nextcloudTheme = theme()
		if (loader && nextcloudTheme) loader.classList.add('askanina-loading--' + nextcloudTheme)
		function loaded() { if (loader) loader.classList.add('askanina-loading--done') }
		frame.addEventListener('load', loaded)
		// A frame that never says it loaded (blocked, offline) does not keep the logo for ever behind it.
		setTimeout(loaded, 20000)

		function send(message) {
			if (frame.contentWindow) frame.contentWindow.postMessage(message, appOrigin)
		}

		var settingsHref = null
		function open(href) {
			if (typeof href !== 'string' || href.indexOf('#/') !== 0) return
			send({ type: 'askanina:navigate', href: href })
			document.body.classList.remove('snapjs-left')
		}

		function draw(message) {
			var items = Array.isArray(message.items) ? message.items : []
			pane.classList.toggle('askanina-nav--empty', items.length === 0)
			pane.setAttribute('aria-label', typeof message.label === 'string' ? message.label : 'AskAnina')
			list.textContent = ''
			var firstPinned = true
			items.forEach(function (item) {
				if (!item || typeof item.href !== 'string') return
				var entry = document.createElement('li')
				if (item.active) entry.classList.add('active')
				if (item.pinned) {
					entry.classList.add('pinned')
					if (firstPinned) entry.classList.add('first-pinned')
					firstPinned = false
				}
				var link = document.createElement('a')
				link.href = '#'
				if (item.active) link.setAttribute('aria-current', 'page')
				link.appendChild(icon(item.icon))
				link.appendChild(document.createTextNode(String(item.label || '')))
				link.addEventListener('click', function (event) {
					event.preventDefault()
					open(item.href)
				})
				entry.appendChild(link)
				list.appendChild(entry)
			})
			var hasSettings = !!(message.settings && typeof message.settings.href === 'string')
			settingsHref = hasSettings ? message.settings.href : null
			if (settingsArea) settingsArea.classList.toggle('askanina-nav--empty', !hasSettings)
			if (hasSettings && settingsButton) settingsButton.textContent = String(message.settings.label || '')
		}

		if (settingsButton) settingsButton.addEventListener('click', function () { open(settingsHref) })

		window.addEventListener('message', function (event) {
			// Only the AskAnina frame itself, from the web app's own origin.
			if (event.origin !== appOrigin || event.source !== frame.contentWindow) return
			var data = event.data
			if (!data || typeof data.type !== 'string') return
			if (data.type === 'askanina:shell-hello' && data.v === PROTOCOL) {
				send({ type: 'askanina:shell-welcome', v: PROTOCOL, appOrigin: appOrigin, theme: nextcloudTheme })
				// Entries sent before this script listened were lost: ask for them again.
				send({ type: 'askanina:navigation-ready' })
				loaded()
			} else if (data.type === 'askanina:navigation') draw(data)
		})

		// The app may have sent its entries before this script ran: ask again once it is there.
		frame.addEventListener('load', function () { send({ type: 'askanina:navigation-ready' }) })
	}

	ready(start)
})()
