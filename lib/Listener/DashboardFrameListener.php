<?php

declare(strict_types=1);

namespace OCA\AskAnina\Listener;

use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IRequest;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

/**
 * The Dashboard's Today widget is a frame of this app's own page, on Nextcloud's own origin: only on
 * the Dashboard, frames from this origin ('self') are allowed. Nothing from elsewhere; every other
 * page keeps Nextcloud's policy as it is.
 *
 * @template-implements IEventListener<Event>
 */
class DashboardFrameListener implements IEventListener {
    public function __construct(private IRequest $request) {
    }

    public function handle(Event $event): void {
        if (!($event instanceof AddContentSecurityPolicyEvent)) {
            return;
        }
        $path = (string) ($this->request->getPathInfo() ?: '');
        if (!str_starts_with($path, '/apps/dashboard')) {
            return;
        }
        $policy = new ContentSecurityPolicy();
        $policy->addAllowedFrameDomain("'self'");
        $event->addPolicy($policy);
    }
}
