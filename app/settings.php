<?php

return [
    'dataPath' => __DIR__ . '/../data',
    'blade' => [
        'views' => __DIR__ . '/../src/Views',
        'cache' => __DIR__ . '/../cache/blade'
    ],
    'timetastic' => [
        'token' => getenv('TIMETASTIC_TOKEN')
    ],
    'email' => [
        'testMode' => getenv('EMAIL_TEST_MODE'),
        'testRecipient' => getenv('EMAIL_TEST_RECIPIENT'),
        'fromName' => 'Rota',
        'fromEmail' => getenv('SMTP_USERNAME'),
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => getenv('SMTP_USERNAME'),
        'password' => getenv('SMTP_PASSWORD'),
        'debug' => false
    ],
    'encryptionKey' => getenv('ENCRYPTION_KEY')
];
