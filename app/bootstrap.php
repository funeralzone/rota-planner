<?php

use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder;
$containerBuilder->addDefinitions(__DIR__ . '/dependencies.php');
$containerBuilder->addDefinitions(['settings' => require __DIR__ . '/settings.php']);

return $containerBuilder->build();
