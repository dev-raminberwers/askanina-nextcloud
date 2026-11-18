<?php

/*
 * SPDX-FileCopyrightText: Ramin Berwers
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace OCA\AskAnina\Controller;

use OCA\AskAnina\Service\AppUrl;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\FeaturePolicy;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Util;

/**
 * The AskAnina page in Nextcloud: Nextcloud's own left pane with AskAnina's menu, and the AskAnina web
 * app in a frame in the rest of the content area. The web app comes from its own address (another
 * origin, AppUrl): this app holds none of its code and passes it no Nextcloud password or session.
 * The two pages only talk by postMessage, each checking the other's origin (js/askanina-shell.js).
 */
class PageController extends Controller {
    public function __construct(string $appName, IRequest $request, private IURLGenerator $urls, private AppUrl $appUrl, private IInitialState $state) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): Response {
        $origin = $this->appUrl->origin();
        $frame = $this->appUrl->frameFor($this->checkUrl());
        if ($origin === null || $frame === null) {
            return new NotFoundResponse();
        }
        // The build names them with their content hash (appinfo/build.json), so a change is never cached.
        $build = json_decode((string) @file_get_contents(__DIR__ . '/../../appinfo/build.json'), true);
        Util::addStyle($this->appName, is_array($build) && is_string($build['style'] ?? null) ? $build['style'] : 'askanina');
        Util::addScript($this->appName, is_array($build) && is_string($build['script'] ?? null) ? $build['script'] : 'askanina-shell');
        $this->state->provideInitialState('shell', ['appOrigin' => $origin]);
        $response = new TemplateResponse($this->appName, 'main', ['src' => $frame]);

        // Nextcloud's page may frame exactly the web app's origin, nothing else.
        $csp = new ContentSecurityPolicy();
        $csp->addAllowedFrameDomain($origin);
        $response->setContentSecurityPolicy($csp);
        // Location, camera and microphone may reach that frame (its allow attribute names them too).
        if (class_exists(FeaturePolicy::class)) {
            $policy = new FeaturePolicy();
            $policy->addAllowedGeoLocationDomain($origin);
            $policy->addAllowedCameraDomain($origin);
            $policy->addAllowedMicrophoneDomain($origin);
            $response->setFeaturePolicy($policy);
        }

        return $response;
    }

    /**
     * Asked by the framed web app (cross-origin, without cookies): this Nextcloud's AskAnina app frames
     * exactly that app origin. Public, holds nothing about any user; only the app origin may read it.
     */
    #[PublicPage]
    #[NoCSRFRequired]
    public function check(): Response {
        $origin = $this->appUrl->origin();
        if ($origin === null) {
            return new JSONResponse(['app' => 'askanina'], Http::STATUS_NOT_FOUND);
        }
        $response = new JSONResponse(['app' => 'askanina', 'v' => 1, 'appOrigin' => $origin]);
        $response->addHeader('Access-Control-Allow-Origin', $origin);
        $response->addHeader('Vary', 'Origin');
        $response->addHeader('Cache-Control', 'no-store');

        return $response;
    }

    private function checkUrl(): string {
        return $this->urls->linkToRouteAbsolute($this->appName . '.page.check');
    }
}
