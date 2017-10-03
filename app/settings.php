<?php

return [
    'dataPath' => __DIR__ . '/../data',
    'blade' => [
        'views' => __DIR__ . '/../src/Views',
        'cache' => __DIR__ . '/../cache/blade'
    ],
    'timetastic' => [
        'token' => getenv('TIMETASTIC_TOKEN') ?: 'e23d15a5-9c0d-4a12-9364-bcd11d053c2a'
    ],
    'email' => [
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
