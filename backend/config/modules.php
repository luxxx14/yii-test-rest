<?php

use common\enums\rbac\PermissionEnum;
use yii2rails\extension\web\helpers\Behavior;

$config = [
    'staff' => 'yubundle\staff\admin\Module',
    'mail' => 'backend\modules\mail\Module',
    'reference' => 'yubundle\reference\admin\Module',
    'logreader' => [
        'class' => 'alyanik\viewlog\Module',
        'as access' => Behavior::access(PermissionEnum::LOGREADER_MANAGE),
    ],
];

$configFile = __DIR__ . '/../../vendor/yubundle/yii2-common/src/project/backend/config/modules.php';
return \yii2rails\extension\common\helpers\Helper::includeConfig($configFile, $config);