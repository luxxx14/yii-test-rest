<?php

$config = [
    //'news' => 'domain\news\v1\Domain',
    'mail' => 'domain\mail\v1\Domain',
    'contact' => 'domain\contact\v1\Domain',
    'staff' => 'yubundle\staff\domain\v1\Domain',

];

$configFile = __DIR__ . '/../../../../../vendor/yubundle/yii2-common/src/project/common/config/domains.php';
return \yii2rails\extension\common\helpers\Helper::includeConfig($configFile, $config);