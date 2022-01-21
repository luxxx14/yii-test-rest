<?php

namespace api\v1\modules\contact\controllers;

use yii2lab\rest\domain\rest\ActiveControllerWithQuery as Controller;
use yii2rails\extension\web\helpers\Behavior;

class PersonalController extends Controller
{

	public $service = 'contact.personal';

	public function behaviors()
    {
        return [
            Behavior::cors(),
            Behavior::auth(),
        ];
    }

}