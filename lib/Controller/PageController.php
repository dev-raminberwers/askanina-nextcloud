<?php

declare(strict_types=1);

namespace OCA\AskAnina\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Util;

/**
 * The AskAnina web app as a Nextcloud page: Nextcloud's own left pane with the app's menu, and the
 * app in a frame in the rest of the content area. In the frame the app keeps its own look
 * (Nextcloud's styles for inputs, labels and headings cannot reach it) and talks to the AskAnina
 * server itself. The build puts the web app in js/app/ (owner 2026-10-06).
 */
class PageController extends Controller {
    /**
     * The frame's policy. The frame runs on Nextcloud's own origin, so a script injected into the app
     * could act as the signed-in Nextcloud user: only the build's own script files may run (no inline
     * scripts, no eval), and only Nextcloud may frame it. Inline styles stay allowed (Vue writes style
     * attributes); data goes to the AskAnina server and the sources the app reads (https only). No
     * sandbox attribute on the frame: without allow-same-origin the app loses its storage.
     * Cast to TV (owner 2026-10-31, docs/CAST.md): Google's Cast sender may load from exactly the three
     * paths below, never all of www.gstatic.com. Should Google move its script elsewhere, only the
     * Cast button goes (the app hides it after 10 s); widen this only after checking the new path.
     */
    private const CAST_SENDER = 'https://www.gstatic.com/cv/js/sender/ https://www.gstatic.com/eureka/clank/ https://www.gstatic.com/cast/sdk/libs/sender/';
    private const FRAME_POLICY = "default-src 'self'; script-src 'self' " . self::CAST_SENDER . "; worker-src 'self' blob:; "
        . "style-src 'self' 'unsafe-inline'; img-src 'self' https: data: blob:; media-src 'self' https: data: blob:; "
        . "font-src 'self' https: data:; connect-src 'self' https: wss: data: blob:; frame-src 'self' https:; "
        . "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'";

    public function __construct(string $appName, IRequest $request, private IURLGenerator $urls) {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): TemplateResponse {
        // The build names them with their content hash (appinfo/build.json), so a change is never cached.
        $build = json_decode((string) @file_get_contents(__DIR__ . '/../../appinfo/build.json'), true);
        Util::addStyle($this->appName, is_array($build) && is_string($build['style'] ?? null) ? $build['style'] : 'askanina');
        // Draws Nextcloud's left pane from the entries the app sends (js/askanina-nav.js).
        Util::addScript($this->appName, is_array($build) && is_string($build['script'] ?? null) ? $build['script'] : 'askanina-nav');
        $response = new TemplateResponse($this->appName, 'main', [
            'src' => $this->urls->linkToRoute($this->appName . '.page.frame'),
        ]);
        $csp = new ContentSecurityPolicy();
        $csp->addAllowedFrameDomain("'self'");
        $response->setContentSecurityPolicy($csp);

        return $response;
    }

    /**
     * The web build's index.html with the policy above. Its files stay where the build put them
     * (js/app/): the page's relative addresses (./assets/...) point there; the scripts load the
     * rest relative to themselves.
     */
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function frame(): Response {
        $html = @file_get_contents(__DIR__ . '/../../js/app/index.html');
        if (!is_string($html) || $html === '') {
            return new NotFoundResponse();
        }
        $base = htmlspecialchars(dirname($this->urls->linkTo($this->appName, 'js/app/index.html')) . '/', ENT_QUOTES);
        $html = (string) preg_replace('/\b(src|href)="\.\//', '$1="' . $base, $html);
        $response = new DataDisplayResponse($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
        // As a header of its own it replaces Nextcloud's page policy, for this document only.
        $response->addHeader('Content-Security-Policy', self::FRAME_POLICY);
        // Should a Nextcloud version build the header from the policy object instead: the same sources.
        $csp = new ContentSecurityPolicy();
        foreach (['https:', 'wss:', 'data:', 'blob:'] as $source) {
            $csp->addAllowedConnectDomain($source);
        }
        foreach (['https:', 'blob:'] as $source) {
            $csp->addAllowedImageDomain($source);
            $csp->addAllowedMediaDomain($source);
        }
        $csp->addAllowedMediaDomain('data:');
        $csp->addAllowedFontDomain('https:');
        $csp->addAllowedWorkerSrcDomain("'self'");
        $csp->addAllowedWorkerSrcDomain('blob:');
        $csp->addAllowedFrameDomain('https:');
        foreach (explode(' ', self::CAST_SENDER) as $source) {
            $csp->addAllowedScriptDomain($source);
        }
        $response->setContentSecurityPolicy($csp);

        return $response;
    }
}
