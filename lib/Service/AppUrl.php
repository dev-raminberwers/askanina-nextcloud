<?php

/*
 * SPDX-FileCopyrightText: Ramin Berwers
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

namespace OCA\AskAnina\Service;

use OCA\AskAnina\AppInfo\Application;
use OCP\IAppConfig;

/**
 * Where the AskAnina web app's frame page is (e.g. https://app.askanina.com/embed.html). Never written
 * into the code: the build puts the address from its configuration in appinfo/shell.json, and an admin
 * may point it elsewhere with `occ config:app:set askanina frame_url --value=<https address>`.
 * Only https (or a local address for development), no user name or password in it.
 */
class AppUrl {
    public const CONFIG_KEY = 'frame_url';

    public function __construct(private IAppConfig $config) {
    }

    /** The frame page's address, or null when neither the admin's nor the build's is valid. */
    public function frameUrl(): ?string {
        $configured = self::clean($this->config->getValueString(Application::APP_ID, self::CONFIG_KEY, ''));
        if ($configured !== null) {
            return $configured;
        }
        $build = json_decode((string) @file_get_contents(__DIR__ . '/../../appinfo/shell.json'), true);
        return is_array($build) && is_string($build['frameUrl'] ?? null) ? self::clean($build['frameUrl']) : null;
    }

    /** The web app's origin (scheme, host and port), the only one this app frames and listens to. */
    public function origin(): ?string {
        $url = $this->frameUrl();
        return $url === null ? null : self::originOf($url);
    }

    /** The frame's address for this Nextcloud: the page plus where the app checks this shell. */
    public function frameFor(string $checkUrl, string $hash = '#/'): ?string {
        $url = $this->frameUrl();
        if ($url === null) {
            return null;
        }
        return $url . '?shell=nextcloud&nc=' . rawurlencode($checkUrl) . $hash;
    }

    /** A clean address, or null: https (http only for localhost), a host, no credentials, query or fragment. */
    public static function clean(string $url): ?string {
        $url = trim($url);
        $parts = parse_url($url);
        if ($url === '' || !is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
            return null;
        }
        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        $local = in_array($host, ['localhost', '127.0.0.1'], true);
        if (!($scheme === 'https' || ($local && $scheme === 'http'))) {
            return null;
        }
        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            return null;
        }
        if (preg_match('/^[a-z0-9.-]+$/', $host) !== 1) {
            return null;
        }
        $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';
        $path = $parts['path'] ?? '/';
        if (preg_match('#^/[A-Za-z0-9._~/-]*$#', $path) !== 1) {
            return null;
        }
        return $scheme . '://' . $host . $port . $path;
    }

    public static function originOf(string $url): string {
        $parts = parse_url($url);
        $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';
        return strtolower((string) $parts['scheme']) . '://' . strtolower((string) $parts['host']) . $port;
    }
}
