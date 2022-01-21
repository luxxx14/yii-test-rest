<?php

use yii2rails\app\domain\enums\YiiEnvEnum;

$url = [
	'frontend' => 'http://web.yumail.kz/',
	'backend' => 'http://admin.yumail.kz/',
	'api' => 'http://api.yumail.kz/',
];

$servers = [
    'smsc' => [
        'login' => '',
        'password' => '',
    ],
	'static' => [
		//'publicPath' => '@frontend/web/',
        /*'domain' => 'http://static.yii/',
        'driver' => 'ftp',
        'connection' => [
            'host' => 'static.yii',
            'username' => 'static',
            'password' => '123456',
        ],*/
		'domain' => $url['frontend'],
		'driver' => 'local',
		'connection' => [
			'path' => '@frontend/web',
		],
	],
	/*'mail' => [
		'host' => '',
		'username' => '',
		'password' => '',
		'port' => 25,
		//'encryption' => 'ssl', // It is often used, check your provider or mail server specs
	],*/
	'db' => [
		'main' => [
            'driver' => 'pgsql',
            'host' => 'yuwert.kz',
            'username' => '',
            'password' => '',
            'dbname' => 'yuwert',
            'defaultSchema' => 'news',
		],
	],
];

$authclientProfiles = [
	'github' => [
		'class' => 'yii\authclient\clients\Github',
		'clientId' => '',
		'clientSecret' => '',
	],
];

$config = [
	'title' => 'Production',
	'filters' => [
		[
			'class' => 'yii2tool\init\domain\filters\input\Custom',
			'title' => 'Custom env config',
			'segment' => null,
			'value' => [
				'url' => $url,
				'servers' => $servers,
				'mode' => [
					'env' => YiiEnvEnum::PROD,
					'debug' => false,
				],
				'jwt' => include(__DIR__ . '/../snipet/jwt.php'),
				/*'authclient' => [
					'profiles' => $authclientProfiles,
				],*/
			],
		],
	],
];

$filters = include(__DIR__ . '/../snipet/filters.php');
$config['filters'] = array_merge($config['filters'], $filters);
return $config;