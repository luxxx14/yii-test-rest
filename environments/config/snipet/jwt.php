<?php

use yii2rails\extension\common\helpers\StringHelper;
use yii2rails\extension\enum\enums\TimeEnum;

return [
	'profiles' => [
		'default' => [
			'key' => StringHelper::generateRandomString(32),
			'issuer_url' => $url['api'] . '/auth',
			'life_time' => TimeEnum::SECOND_PER_MINUTE * 20,
			'allowed_algs' => ['HS256', 'SHA512', 'HS384'],
			'default_alg' => 'HS256',
			'audience' => [$url['api']],
		],
		'auth' => [
			'key' => StringHelper::generateRandomString(32),
			'issuer_url' => $url['api'] . '/auth',
			'life_time' => TimeEnum::SECOND_PER_MINUTE * 20,
			'allowed_algs' => ['HS256', 'SHA512', 'HS384'],
			'default_alg' => 'HS256',
			'audience' => [$url['api']],
		],
	],
];
