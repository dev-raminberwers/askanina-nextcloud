<?php
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
	<!-- The breathing logo until the frame's page is there (css/askanina.css, owner 2026-11-11). -->
	<div id="askanina-loading" aria-hidden="true"><span></span></div>
	<iframe id="askanina-frame" src="<?php p($_['src']); ?>" title="AskAnina" allow="geolocation; clipboard-write"></iframe>
</div>
