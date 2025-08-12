<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/live',
        __DIR__ . '/tests/php',
    ]);

    if (file_exists(__DIR__ . '/phpstan.neon')) {
        $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');
    }

    $rectorConfig->sets([
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
    ]);
};


