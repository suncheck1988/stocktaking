<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$appEnv = (string)getenv('APP_ENV');
if ($appEnv === 'test') {
    $appEnv = 'dev';
}

$aggregator = new ConfigAggregator([
    new PhpFileProvider(__DIR__ . '/common/*.php'),
    new PhpFileProvider(__DIR__ . '/' . $appEnv . '/*.php'),
]);

return $aggregator->getMergedConfig();
