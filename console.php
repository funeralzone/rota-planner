#!/usr/bin/env php
<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use ChrisHarrison\RotaPlanner\Commands\BuildCommand;

/* @var ContainerInterface $container */
$container = require __DIR__ . '/app/bootstrap.php';

$application = new Application();
$application->add($container->get(BuildCommand::class));
$application->run();
