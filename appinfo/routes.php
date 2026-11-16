<?php

declare(strict_types=1);

// One page: the AskAnina web app. Everything else goes straight to the AskAnina server.
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        // The web app's own page in the frame, with a strict Content Security Policy.
        ['name' => 'page#frame', 'url' => '/frame', 'verb' => 'GET'],
    ],
];
