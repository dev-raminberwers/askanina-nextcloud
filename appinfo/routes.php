<?php

/*
 * SPDX-FileCopyrightText: Ramin Berwers
 * SPDX-License-Identifier: Apache-2.0
 */

declare(strict_types=1);

// The AskAnina page (a frame of the AskAnina web app) and the check the framed app asks.
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        // The framed app asks this (CORS, no cookies) to learn that this Nextcloud frames it.
        ['name' => 'page#check', 'url' => '/check', 'verb' => 'GET'],
    ],
];
