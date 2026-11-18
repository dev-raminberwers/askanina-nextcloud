<?php

/*
 * SPDX-FileCopyrightText: Ramin Berwers
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace OCA\AskAnina\Listener;

use OCA\AskAnina\Service\AppUrl;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IRequest;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

/**
 * The Dashboard's Today widget frames the AskAnina web app: only on the Dashboard, and only that one
 * origin. Every other page keeps Nextcloud's policy as it is.
 *
 * @template-implements IEventListener<Event>
 */
class DashboardFrameListener implements IEventListener {
    public function __construct(private IRequest $request, private AppUrl $appUrl) {
    }

    public function handle(Event $event): void {
        if (!($event instanceof AddContentSecurityPolicyEvent)) {
            return;
        }
        $path = (string) ($this->request->getPathInfo() ?: '');
        if (!str_starts_with($path, '/apps/dashboard')) {
            return;
        }
        $origin = $this->appUrl->origin();
        if ($origin === null) {
            return;
        }
        $policy = new ContentSecurityPolicy();
        $policy->addAllowedFrameDomain($origin);
        $event->addPolicy($policy);
    }
}
