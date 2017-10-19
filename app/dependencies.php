<?php

use ChrisHarrison\RotaPlanner\Commands\BuildCommand;
use ChrisHarrison\RotaPlanner\Model\Services\RotaGenerator;
use ChrisHarrison\RotaPlanner\Model\Services\IdGeneratorInterface;
use ChrisHarrison\RotaPlanner\Model\Services\IdGenerator;
use ChrisHarrison\RotaPlanner\Model\Repositories\RotaRepositoryInterface;
use ChrisHarrison\RotaPlanner\Persistence\RotaRepository;
use ChrisHarrison\RotaPlanner\Model\Repositories\MemberRepositoryInterface;
use ChrisHarrison\JsonRepository\Repositories\EncryptedJsonRepository;
use ChrisHarrison\JsonRepository\Repositories\JsonRepository;
use DI\Container;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local as LocalAdapter;
use ChrisHarrison\RotaPlanner\Persistence\MemberRepository;
use ChrisHarrison\RotaPlanner\Model\Services\IncrementingNumber;
use Philo\Blade\Blade;
use ChrisHarrison\TimetasticAPI\Client as TimetasticClient;
use ChrisHarrison\TimetasticAPI\HttpClient as TimetasticHttpClient;
use PHPMailer\PHPMailer\PHPMailer;
use Phlib\Encrypt\EncryptorInterface;
use ChrisHarrison\RotaPlanner\Services\Notifier;
use ChrisHarrison\RotaPlanner\Services\TestingNotifier;
use ChrisHarrison\RotaPlanner\Services\EmailNotifier;

return [
    'DataFilesystem' => function (Container $c) {
        return new Filesystem(new LocalAdapter($c->get('settings')['dataPath']));
    },
    BuildCommand::class => \DI\object(BuildCommand::class),
    RotaGenerator::class => \DI\object(RotaGenerator::class),
    IdGeneratorInterface::class => \DI\object(IdGenerator::class),
    EncryptorInterface::class => function (Container $c) {
        return new Phlib\Encrypt\Encryptor\OpenSsl($c->get('settings')['encryptionKey']);
    },
    RotaRepositoryInterface::class => function (Container $c) {
        return new RotaRepository(
            new JsonRepository(
                $c->get('DataFilesystem'),
                'rotas.json'
            ),
            $c->get(MemberRepositoryInterface::class)
        );
    },
    MemberRepositoryInterface::class => function (Container $c) {
        return new MemberRepository(
            new EncryptedJsonRepository(
                $c->get('DataFilesystem'),
                'members.json',
                $c->get(EncryptorInterface::class),
                ['email']
            )
        );
    },
    IncrementingNumber::class => new IncrementingNumber(time()),
    Blade::class => function (Container $c) {
        $views = $c->get('settings')['blade']['views'];
        $cache = $c->get('settings')['blade']['cache'];
        return new Blade($views, $cache);
    },
    TimetasticClient::class => function (Container $c) {
        return new TimetasticClient(new TimetasticHttpClient($c->get('settings')['timetastic']['token']));
    },
    PHPMailer::class => function (Container $c) {
        $mailer = new PHPMailer();
        $mailer->isHTML(true);
        $mailer->setFrom($c->get('settings')['email']['fromEmail'], $c->get('settings')['email']['fromName']);
        $mailer->isSMTP();
        if ($c->get('settings')['email']['debug']) {
            $mailer->SMTPDebug = 2;
        }
        $mailer->Host = $c->get('settings')['email']['host'];
        $mailer->Port = $c->get('settings')['email']['port'];
        $mailer->SMTPSecure = 'tls';
        $mailer->SMTPAuth = true;
        $mailer->Username = $c->get('settings')['email']['username'];
        $mailer->Password = $c->get('settings')['email']['password'];

        return $mailer;
    },
    Notifier::class => function (Container $c) {
        if ($c->get('settings')['email']['testMode']) {
            return new TestingNotifier($c->get(PHPMailer::class), $c->get('settings')['email']['testRecipient']);
        } else {
            return new EmailNotifier($c->get(PHPMailer::class));
        }
    }
];