<?php

declare(strict_types=1);

namespace OCA\AskAnina\Dashboard;

use OCA\AskAnina\AppInfo\Application;
use OCP\Dashboard\IIconWidget;
use OCP\Dashboard\IWidget;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Util;

/**
 * "AskAnina Today" on Nextcloud's Dashboard (owner 2026-11-08): the next appointments of today with
 * their times and leave-by line. The appointments are sealed end to end with keys only the user's
 * browsers and phones hold, so this server can never read or draw them (not an IAPIWidget: it has
 * no items to give). The widget is a small frame of the app's own Today page (#/widget/today, the
 * same frame page as the app with its strict policy), worked out in the browser with the user's keys.
 */
class TodayWidget implements IWidget, IIconWidget {
    public function __construct(private IL10N $l10n, private IURLGenerator $urls) {
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
        // Nextcloud's own calendar icon where a version shows the class; the app's icon below otherwise (IIconWidget).
        return 'icon-calendar-dark';
    }

    public function getIconUrl(): string {
        return $this->urls->getAbsoluteURL($this->urls->imagePath(Application::APP_ID, 'app.svg'));
    }

    public function getUrl(): ?string {
        return $this->urls->linkToRouteAbsolute(Application::APP_ID . '.page.index');
    }

    public function load(): void {
        // The script carries its content hash in its name (appinfo/build.json), as the pane's.
        $build = json_decode((string) @file_get_contents(__DIR__ . '/../../appinfo/build.json'), true);
        Util::addScript(Application::APP_ID, is_array($build) && is_string($build['dashboard'] ?? null) ? $build['dashboard'] : 'askanina-dashboard');
    }
}
