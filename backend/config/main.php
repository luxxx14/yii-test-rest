<?php

$config = [

];

$configFile = __DIR__ . '/../../vendor/yubundle/yii2-common/src/project/backend/config/main.php';
return \yii2rails\extension\common\helpers\Helper::includeConfig($configFile, $config);