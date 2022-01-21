<?php

namespace api\v1\modules\mail\controllers;

use yii2lab\rest\domain\rest\ActiveControllerWithQuery as Controller;
use yii2rails\extension\web\helpers\Behavior;

class FolderController extends Controller
{
	public $service = 'mail.folder';

    public function behaviors()
    {
        return [
            Behavior::cors(),
            Behavior::auth(),
        ];
    }

}