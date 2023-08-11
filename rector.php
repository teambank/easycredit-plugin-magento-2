<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\DowngradeLevelSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__,
    ]);
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_74,
        DowngradeLevelSetList::DOWN_TO_PHP_74,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
    ]);

    $rectorConfig->skip([
        FinalizeClassesWithoutChildrenRector::class,
        ExplicitBoolCompareRector::class,
        RemoveUselessReturnTagRector::class => [
            __DIR__ . '/Api/*',
        ],
        RemoveUselessParamTagRector::class => [
            __DIR__ . '/Api/*',
        ],
        UnSpreadOperatorRector::class => [
            __DIR__ . '/Plugin/*',
        ],
    ]);
};
