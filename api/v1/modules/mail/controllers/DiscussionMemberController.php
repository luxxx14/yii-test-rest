<?php

namespace api\v1\modules\mail\controllers;

use yii2lab\rest\domain\rest\ActiveControllerWithQuery as Controller;
use yii2rails\extension\web\helpers\Behavior;

class DiscussionMemberController extends Controller
{
	public $service = 'mail.discussionMember';

    public $formClass = null;
    public $titleName = null;

    public function actions()
    {
        $actions = parent::actions();
        return $actions;
    }

    public function behaviors()
    {
        return [
            Behavior::cors(),
            Behavior::auth(),
        ];
    }

}