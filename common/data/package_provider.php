<?php

use yii\helpers\ArrayHelper;

$collection = [

];

$baseCollection = require_once(__DIR__ . '/../../vendor/yubundle/yii2-common/src/package/domain/data/package_provider.php');
return ArrayHelper::merge($baseCollection, $collection);