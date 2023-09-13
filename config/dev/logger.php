<?php

declare(strict_types=1);

return [
    'config' => [
        'logger' => [
            'debug' => true,
            'file' => __DIR__ . '/../../var/log/' . PHP_SAPI . '/application.log',
            'stderr' => true,
        ],
    ],
];
