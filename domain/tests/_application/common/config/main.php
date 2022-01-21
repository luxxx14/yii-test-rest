<?php

$config = [

];

//$parentConfig = include(__DIR__ . '/../../../../../vendor/yii2tool/yii2-test/src/base/_application/common/config/main.php');
$configFile = __DIR__ . '/../../../../../vendor/yubundle/yii2-common/src/project/common/config/main.php';
return \yii2rails\extension\common\helpers\Helper::includeConfig($configFile, $config);