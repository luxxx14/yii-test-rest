<?php

namespace backend\modules\mail\controllers;

use backend\modules\mail\forms\DomainForm;
use yii2rails\domain\data\Query;
use yii2rails\domain\web\ActiveController as Controller;

class DomainController extends Controller
{

    const RENDER_INDEX = '@backend/modules/mail/views/domain/index';

    public $formClass = DomainForm::class;

    public $service = 'mail.companyDomain';

    public function actions() {
        $actions = parent::actions();
        $actions['index']['render'] = self::RENDER_INDEX;
        $actions['index']['query'] = Query::forge()->with('company');
        return $actions;
    }

}
