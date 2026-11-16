<?php

declare(strict_types=1);

namespace OCA\AskAnina\AppInfo;

use OCA\AskAnina\Dashboard\TodayWidget;
use OCA\AskAnina\Listener\DashboardFrameListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

/** AskAnina in Nextcloud: a thin wrapper around the web app (nextcloud/README.md). */
class Application extends App implements IBootstrap {
    public const APP_ID = 'askanina';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        // "AskAnina Today" on the Dashboard (owner 2026-11-08): a frame of the app's Today, which the
        // Dashboard may show only with frames from this origin allowed there.
        $context->registerDashboardWidget(TodayWidget::class);
        $context->registerEventListener(AddContentSecurityPolicyEvent::class, DashboardFrameListener::class);
    }

    public function boot(IBootContext $context): void {
    }
}
