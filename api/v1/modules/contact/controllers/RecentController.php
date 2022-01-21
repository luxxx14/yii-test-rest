<?php

namespace api\v1\modules\contact\controllers;

use yii2lab\rest\domain\rest\ActiveControllerWithQuery as Controller;
use yii2lab\rest\domain\rest\IndexActionWithQuery;
use yii2rails\extension\web\helpers\Behavior;

class RecentController extends Controller
{

	public $service = 'contact.personal';

	public function behaviors()
    {
        return [
            Behavior::cors(),
            Behavior::auth(),
        ];
    }

    public function actions()
    {
        return [
            'index' => [
                'class' => IndexActionWithQuery::class,
                'serviceMethod' => 'allRecent',
            ],
        ];
    }


}