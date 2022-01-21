<?php

use yii2rails\extension\yii\helpers\FileHelper;

$testDir = FileHelper::up(__DIR__, 3);

Yii::setAlias('@tests', $testDir);
