/*
 * SPDX-FileCopyrightText: Ramin Berwers
 * SPDX-License-Identifier: Apache-2.0
 */
/**
 * "AskAnina Today" on Nextcloud's Dashboard (lib/Dashboard/TodayWidget.php): the AskAnina web app's
 * Today page in a small frame from its own address (another origin). The frame says hello and gets
 * Nextcloud's theme; a tap on "Open AskAnina" asks this page to open the AskAnina page in Nextcloud.
 * Only messages from that frame and the web app's origin count. See README.md.
 */
(function () {
	'use strict'

	var PROTOCOL = 1

	function settings() {
		try {
			if (window.OCP && OCP.InitialState && typeof OCP.InitialState.loadState === 'function') return OCP.InitialState.loadState('askanina', 'widget')
		} catch (error) {
			// Fall back to the element Nextcloud writes the state into.
		}
		var element = document.getElementById('initial-state-askanina-widget')
		if (!element) return null
		try {
			return JSON.parse(atob(element.value))
		} catch (error) {
			return null
		}
	}

	function theme() {
		var body = document.body
		if (!body) return null
		var themes = String(body.getAttribute('data-themes') || '').split(',').map(function (item) { return item.trim() })
		if (themes.some(function (item) { return item.indexOf('dark') === 0 }) || body.hasAttribute('data-theme-dark')) return 'dark'
		if (themes.some(function (item) { return item.indexOf('light') === 0 }) || body.hasAttribute('data-theme-light')) return 'light'
		return null
	}

	function register() {
		var state = settings()
		if (!state || typeof state.appOrigin !== 'string' || typeof state.frame !== 'string') return
		if (!window.OCA || !OCA.Dashboard || typeof OCA.Dashboard.register !== 'function') return
		OCA.Dashboard.register('askanina-today', function (element) {
			var frame = document.createElement('iframe')
			frame.src = state.frame
			frame.title = 'AskAnina'
			frame.setAttribute('loading', 'lazy')
			frame.setAttribute('referrerpolicy', 'no-referrer')
			frame.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox')
			frame.style.width = '100%'
			frame.style.height = '100%'
			frame.style.minHeight = '320px'
			frame.style.border = '0'
			frame.style.display = 'block'
			frame.style.background = 'transparent'
			element.textContent = ''
			element.appendChild(frame)

			window.addEventListener('message', function (event) {
				if (event.origin !== state.appOrigin || event.source !== frame.contentWindow) return
				var data = event.data
				if (!data || typeof data.type !== 'string') return
				if (data.type === 'askanina:shell-hello' && data.v === PROTOCOL) frame.contentWindow.postMessage({ type: 'askanina:shell-welcome', v: PROTOCOL, appOrigin: state.appOrigin, theme: theme() }, state.appOrigin)
				else if (data.type === 'askanina:open-app' && typeof state.page === 'string') window.location.href = state.page
			})
		})
	}

	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', register)
	else register()
})()
