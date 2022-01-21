<?php

$config = [

];

$parentConfig = include(__DIR__ . '/../../../../../vendor/yubundle/yii2-common/src/project/common/config/domains.php');
return \yii\helpers\ArrayHelper::merge($parentConfig, $config);