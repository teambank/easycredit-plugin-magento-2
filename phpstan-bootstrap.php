<?php

declare(strict_types=1);

$magentoDir = getenv('MAGENTO_DIR');
if ($magentoDir === false || $magentoDir === '') {
    throw new RuntimeException('MAGENTO_DIR must be set (e.g. /opt/magento) for PHPStan bootstrap.');
}

require_once $magentoDir . '/vendor/autoload.php';
