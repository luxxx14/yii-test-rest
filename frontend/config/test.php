<?php

$config = [

];

$configFile = __DIR__ . '/../../vendor/yubundle/yii2-common/src/project/frontend/config/test.php';
return \yii2rails\extension\common\helpers\Helper::includeConfig($configFile, $config);