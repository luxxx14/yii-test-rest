<?php

namespace domain\contact\v1\entities;

use yii2lab\geo\domain\validators\PhoneValidator;
use yii2rails\domain\BaseEntity;
use yii2rails\domain\exceptions\UnprocessableEntityHttpException;
use yii2rails\domain\helpers\ErrorCollection;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;
use yubundle\account\domain\v2\values\PersonIdValue;

/**
 * Class PersonalEntity
 * 
 * @package domain\contact\v1\entities
 * 
 * @property $id
 * @property $person_id
 * @property $first_name
 * @property $last_name
 * @property $middle_name
 * @property $phone
 * @property $email
 * @property $status
 * @property $kind
 * @property $updated_at
 * @property $created_at
 */
class PersonalEntity extends BaseEntity {

	protected $id;
	protected $person_id;
    protected $first_name;
    protected $last_name;
    protected $middle_name;
	protected $phone;
	protected $email;
	protected $kind;
	protected $status = StatusEnum::ENABLE;
	protected $updated_at;
	protected $created_at;
	protected $full_name;

    public function init() {
        parent::init();
        $this->created_at = new TimeValue;
        $this->created_at->setNow();
        $this->updated_at = new TimeValue;
        $this->updated_at->setNow();
        $this->person_id = new PersonIdValue;
    }

    public function fieldType()
    {
        return [
            'id' => 'integer',
            'person_id' => 'integer',
            'first_name' => 'string',
            'last_name' => 'string',
            'middle_name' => 'string',
            'phone' => 'string',
            'email' => 'string',
            'status' => 'integer',
            'kind' => 'string',
            'created_at' => TimeValue::class,
            'updated_at' => TimeValue::class,
        ];
    }

    public function rules()
    {
        return [
            [['first_name', 'last_name', 'middle_name', 'phone', 'email'], 'trim'],
            [['first_name', 'last_name', 'middle_name'], 'filter','filter'=>'\yii\helpers\HtmlPurifier::process'],
            [['person_id', 'first_name'], 'required'],
            [['email'], 'email'],
            ['status', 'in', 'range' => StatusEnum::values()],
        ];
    }

    public function beforeValidate()
    {
        $isValid = parent::beforeValidate();
        if(empty($this->email) | $this->email == '') {
            $errors = new ErrorCollection;
            $errors->add('email', 'contact/personal','email_required');
            throw new UnprocessableEntityHttpException($errors);
        }
        return $isValid;
    }

    public function getFullName() {
        $fullName = $this->first_name . SPC . $this->last_name;
        return trim($fullName);
    }
}
