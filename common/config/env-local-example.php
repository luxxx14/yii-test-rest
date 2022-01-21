<?php

return [
    'mail' => [
        'box' => [
            'defaultSize' => 104857600,
        ],
        'isCorparate' => true,
    ],
    'account' => [
        'login' => [
            'defaultCompanyId' => 3, // ID компании по умолчанию
        ],
        'registration' => [
            'access' => 'open', // доступность регистрации (open,invite,close)
            'defaultStatus' => 1, // начальный статус пользователя (если 0, то только по премодерации)
        ],
        'confirm' => [
            'smsInterval' => 60, // интервал отправки СМС с подтверждением в секундах
        ],
        'auth' => [
            'security' => [
                'attemptCount' => 3,
                'attemptExpire' => \yii2rails\extension\enum\enums\TimeEnum::SECOND_PER_MINUTE * 30,
                'blockExpire' => \yii2rails\extension\enum\enums\TimeEnum::SECOND_PER_MINUTE * 30,
            ],
        ],
    ],
    'cors' => [
        'credentials' => true,
    ],
    'test' => [
        'mode' => 'dev', // режим сервера: dev, test, prod
        'skipBug' => true, // скрывать кейсы, помеченные как баг
        //'dumpDangerResponse' => true, // показывать дамп, когда приходит 500 статус-код
        'mail' => [
            'pop3' => [
                'host' => 'pop.mail.ru',
                'username' => 'autotest.yumail@mail.ru',
                'password' => 'we23y&BT76f^5D%4s26d',
            ],
            'smtp' => [
                'host' => 'ssl://smtp.mail.ru',
                'username' => 'autotest.yumail@mail.ru',
                'password' => 'we23y&BT76f^5D%4s26d',
                'port' => '465',
            ],
        ],
    ],
    'url' => [
        'frontend' => 'http://yumail.project/',
        'backend' => 'http://admin.yumail.project/',
        'api' => 'http://api.yumail.project/',
    ],
    'servers' => [
        'storage' => [
            'resourceHost' => 'http://yumail.project/',
        ],
        /*'storage' => [
            'apiHost' => 'http://api.storage.srv/',
            'resourceHost' => 'http://yumail.project/',
            'driver' => 'core',
        ],*/
        'static' => [
            'publicPath' => '@frontend/web/',
            'domain' => 'http://yumail.project/',
            'driver' => 'local',
            'connection' => [
                'path' => '@frontend/web',
            ],
        ],
        'db' => [
            'main' => [
                'driver' => 'pgsql',
                'username' => 'postgres',
                'password' => 'postgres',
                'dbname' => 'yumail',
                'defaultSchema' => 'news',
            ],
        ],
        /*'mail' => [
            'class' => 'Swift_SmtpTransport',
            'host' => '',
            'username' => 'admin@yuwert.kz',
            'password' => '',
            'port' => '587',
            'encryption' => 'tls',
        ],*/
    ],
    'cache' => [
        //'enable' => true, //YII_ENV == YiiEnvEnum::PROD,
        //'enableDomainCache' => true,
    ],
    'mode' => [
        'env' => 'dev',
        'debug' => true,
        //'benchmark' => true,
    ],
    'jwt' => [
        'profiles' => [
            'partner_yumail' => [
                'key' => 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz',
            ],
            'auth' => [
                'key' => 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz',
                'lifetime' => \yii2rails\extension\enum\enums\TimeEnum::SECOND_PER_YEAR,
            ],
        ],
    ],
    'domain' => [
        'driver' => [
            'primary' => 'ar',
            'slave' => 'ar',
        ],
    ],
    'cookieValidationKey' => [
        'frontend' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'backend' => 'qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq',
    ],
];
