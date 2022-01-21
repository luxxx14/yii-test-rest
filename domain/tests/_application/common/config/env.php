<?php

$config = [


];

$configFile = __DIR__ . '/../../../../../common/config/env.php';
return \yii2rails\extension\common\helpers\Helper::includeConfig($configFile, $config);