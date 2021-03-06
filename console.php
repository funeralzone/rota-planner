#!/usr/bin/env php
<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use ChrisHarrison\RotaPlanner\Commands\DailyCommand;
use ChrisHarrison\RotaPlanner\Commands\BuildCommand;
use ChrisHarrison\RotaPlanner\Commands\SyncMembersCommand;
use ChrisHarrison\RotaPlanner\Commands\RemindCommand;

/* @var ContainerInterface $container */
$container = require __DIR__ . '/app/bootstrap.php';

$application = new Application();
$application->add($container->get(DailyCommand::class));
$application->add($container->get(BuildCommand::class));
$application->add($container->get(SyncMembersCommand::class));
$application->add($container->get(RemindCommand::class));
$application->run();
