<?php

$config = [
    'name' => 'Yumail',
];

$configFile = __DIR__ . '/../../vendor/yubundle/yii2-common/src/project/common/config/main.php';
return \yii2rails\extension\common\helpers\Helper::includeConfig($configFile, $config);