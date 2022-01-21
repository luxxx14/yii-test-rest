<?php

namespace backend\modules\mail\forms;

use Yii;
use yii2rails\domain\base\Model;

class DomainForm extends Model {
	
	public $domain;
	public $host;
	public $port;
	public $company_id;

	public function attributeLabels()
	{
		return [
			'domain' => Yii::t('mail/domain', 'domain'),
			'host' => Yii::t('mail/domain', 'host'),
            'port' => Yii::t('mail/domain', 'port'),
            'company_id' => Yii::t('mail/domain', 'company'),
		];
	}
}
