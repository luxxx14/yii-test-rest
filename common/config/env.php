<?php

$config = [
    'mail' => [
        'box' => [
            'defaultSize' => 104857600,
        ],
    ],
    'account' => [
        'login' => [
            'defaultCompanyId' => 1,
        ],
        'auth' => [
            'security' => [
                'attemptCount' => 3,
                'attemptExpire' => \yii2rails\extension\enum\enums\TimeEnum::SECOND_PER_MINUTE * 30,
                'blockExpire' => \yii2rails\extension\enum\enums\TimeEnum::SECOND_PER_MINUTE * 30,
            ],
        ],
    ],
	'servers' => [
        /*'storage' => [
            'apiHost' => 'http://api.storage.srv/',
            'resourceHost' => 'http://storage.srv/',
            'driver' => 'core',
        ],*/
		'db' => [
            'test' => [
                'driver' => 'pgsql',
                'username' => 'postgres',
                'password' => 'postgres',
                'dbname' => 'yumail',
                'defaultSchema' => 'news',
            ],
			'main' => [
				'map' => [
                    'rest_collection' => 'mail.rest',

                    'mail_attachment' => 'mail.attachment',
                    'mail_box' => 'mail.box',
                    'mail_discussion' => 'mail.discussion',
                    'mail_discussion_mail' => 'mail.discussions_mail',
                    'mail_discussion_member' => 'mail.discussion_member',
                    'mail_dialog' => 'mail.dialogs',
                    'mail_domain' => 'mail.domain',
                    'mail_mail' => 'mail.mail',
                    'mail_flow' => 'mail.flow',
                    'mail_folder' => 'mail.folder',
                    'mail_settings' => 'mail.settings',
					
                    'staff_division' => 'staff.divisions',
                    'staff_worker' => 'staff.workers',
                    'contact_personal' => 'main.contacts',

                    'company' => 'main.companies',
				],
			],
		],
	],
];

$configFile = __DIR__ . '/../../vendor/yubundle/yii2-common/src/project/common/config/env.php';
return \yii2rails\extension\common\helpers\Helper::includeConfig($configFile, $config);
