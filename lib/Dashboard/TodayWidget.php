<?php

/*
 * SPDX-FileCopyrightText: Ramin Berwers
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace OCA\AskAnina\Dashboard;

use OCA\AskAnina\AppInfo\Application;
use OCA\AskAnina\Service\AppUrl;
use OCP\AppFramework\Services\IInitialState;
use OCP\Dashboard\IIconWidget;
use OCP\Dashboard\IWidget;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Util;

/**
 * "AskAnina Today" on Nextcloud's Dashboard: the AskAnina web app's own Today page (#/widget/today) in a
 * small frame. The appointments are sealed end to end with keys only the user's browsers and phones
 * hold, so this server never reads or draws them; the frame shows them in the browser.
 */
class TodayWidget implements IWidget, IIconWidget {
    public function __construct(private IL10N $l10n, private IURLGenerator $urls, private AppUrl $appUrl, private IInitialState $state) {
    }

    public function getId(): string {
        return 'askanina-today';
    }

    public function getTitle(): string {
        return $this->l10n->t('AskAnina Today');
    }

    public function getOrder(): int {
        return 10;
    }

    public function getIconClass(): string {
        return 'icon-calendar-dark';
    }

    public function getIconUrl(): string {
        return $this->urls->getAbsoluteURL($this->urls->imagePath(Application::APP_ID, 'app.svg'));
    }

    public function getUrl(): ?string {
        return $this->urls->linkToRouteAbsolute(Application::APP_ID . '.page.index');
    }

    public function load(): void {
        $origin = $this->appUrl->origin();
        $frame = $this->appUrl->frameFor($this->urls->linkToRouteAbsolute(Application::APP_ID . '.page.check'), '#/widget/today');
        if ($origin === null || $frame === null) {
            return;
        }
        $this->state->provideInitialState('widget', ['appOrigin' => $origin, 'frame' => $frame, 'page' => $this->getUrl()]);
        $build = json_decode((string) @file_get_contents(__DIR__ . '/../../appinfo/build.json'), true);
        Util::addScript(Application::APP_ID, is_array($build) && is_string($build['dashboard'] ?? null) ? $build['dashboard'] : 'askanina-dashboard');
    }
}
