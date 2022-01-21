<?php

$config = [

];

//$parentConfig = include(__DIR__ . '/../../../../../vendor/yii2tool/yii2-test/src/base/_application/common/config/main.php');
$parentConfig = include(__DIR__ . '/../../../../../vendor/yubundle/yii2-common/src/project/common/config/main.php');
return \yii\helpers\ArrayHelper::merge($parentConfig, $config);