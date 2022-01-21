<?php

$config = [


];

$parentConfig = include(__DIR__ . '/../../../../../common/config/env.php');
return \yii\helpers\ArrayHelper::merge($parentConfig, $config);