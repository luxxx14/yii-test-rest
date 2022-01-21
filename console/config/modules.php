<?php

$config = [
    'user' => 'yubundle\user\api\v1\Module',
    'report' => 'console\modules\report\Module',

];

$configFile = __DIR__ . '/../../vendor/yubundle/yii2-common/src/project/console/config/modules.php';
return \yii2rails\extension\common\helpers\Helper::includeConfig($configFile, $config);