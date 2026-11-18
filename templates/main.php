<?php
/*
 * SPDX-FileCopyrightText: Ramin Berwers
 * SPDX-License-Identifier: Apache-2.0
 */
/** @var array $_ */
?>
<div id="app-navigation" class="app-navigation-personal askanina-nav--empty" role="navigation">
	<ul id="askanina-nav-list"></ul>
	<div id="app-settings" class="askanina-nav--empty">
		<div id="app-settings-header">
			<button type="button" class="settings-button" id="askanina-nav-settings"></button>
		</div>
	</div>
</div>
<div id="app-content">
	<div id="askanina-loading" aria-hidden="true"><span></span></div>
	<!-- The AskAnina web app from its own address (another origin): it cannot reach this page or Nextcloud. -->
	<iframe id="askanina-frame" src="<?php p($_['src']); ?>" title="AskAnina"
		allow="geolocation; clipboard-write; microphone; camera"
		sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox allow-downloads allow-modals allow-top-navigation-by-user-activation"
		referrerpolicy="no-referrer"></iframe>
</div>
