<?php

use ChrisHarrison\RotaPlanner\Commands\GenerateCommand;
use ChrisHarrison\RotaPlanner\Model\Services\RotaGenerator;
use ChrisHarrison\RotaPlanner\Model\Services\IdGeneratorInterface;
use ChrisHarrison\RotaPlanner\Model\Services\IdGenerator;
use ChrisHarrison\RotaPlanner\Persistence\RotaRepositoryInterface;
use ChrisHarrison\RotaPlanner\Persistence\RotaRepository;
use ChrisHarrison\RotaPlanner\Persistence\MemberRepositoryInterface;
use ChrisHarrison\JsonRepository\Persistence\JsonRepository;
use DI\Container;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use ChrisHarrison\RotaPlanner\Persistence\MemberRepository;
use ChrisHarrison\RotaPlanner\Model\Services\IncrementingNumber;
use ChrisHarrison\ControllerBuilder\ControllerBuilderInterface;
use ChrisHarrison\ControllerBuilder\ControllerBuilder;
use Philo\Blade\Blade;

return [
    'BuildFilesystem' => function (Container $c) {
        return new Filesystem(new LocalAdapter($c->get('settings')['buildPath']));
    },
    'DataFilesystem' => function (Container $c) {
        return new Filesystem(new LocalAdapter($c->get('settings')['dataPath']));
    },
    GenerateCommand::class => \DI\object(GenerateCommand::class),
    RotaGenerator::class => \DI\object(RotaGenerator::class),
    IdGeneratorInterface::class => \DI\object(IdGenerator::class),
    RotaRepositoryInterface::class => function (Container $c) {
        return new RotaRepository(
            new JsonRepository($c->get('DataFilesystem'), 'rotas.json'),
            $c->get(MemberRepositoryInterface::class)
        );
    },
    MemberRepositoryInterface::class => function (Container $c) {
        return new MemberRepository(
            new JsonRepository($c->get('DataFilesystem'), 'members.json')
        );
    },
    IncrementingNumber::class => new IncrementingNumber(time()),
    ControllerBuilderInterface::class => function (Container $c) {
        return new ControllerBuilder($c->get('BuildFilesystem'));
    },
    Blade::class => function (Container $c) {
        $views = $c->get('settings')['blade']['views'];
        $cache = $c->get('settings')['blade']['cache'];
        return new Blade($views, $cache);
    },
];