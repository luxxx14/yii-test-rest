<?php

namespace domain\mail\v1\entities;

use App;
use domain\contact\v1\entities\PersonalEntity;
use domain\mail\v1\enums\MemberEnum;
use yii\web\NotFoundHttpException;
use yii2rails\domain\BaseEntity;
use yii2rails\domain\behaviors\entity\TimeValueFilter;
use yii2rails\domain\data\Query;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;

/**
 * Class DiscussionMemberEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $id
 * @property $discussion_id
 * @property $email
 * @property $new_message_count
 * @property $role
 * @property $created_at
 * @property $updated_at
 * @property $status
 * @property $fillable
 */
class DiscussionMemberEntity extends BaseEntity
{

    protected $id;
    protected $discussion_id;
    protected $email;
    protected $new_message_count = 0;
    protected $role = MemberEnum::MEMBER;
    protected $full_name;
    protected $created_at;
    protected $updated_at;
    protected $status = StatusEnum::ENABLE;

    public $fillable = ['role'];

    public function behaviors()
    {
        return [
            [
                'class' => TimeValueFilter::class,
            ],
        ];
    }

    public function fieldType()
    {
        return [
            'id' => 'integer',
            'discussion_id' => 'integer',

            'status' => 'integer',
            'created_at' => TimeValue::class,
            'updated_at' => TimeValue::class,
        ];
    }

    public function rules()
    {
        return [
            [['email',], 'trim'],
            [['email',], 'email'],
            [['discussion_id', 'email',], 'required'],
            ['status', 'in', 'range' => StatusEnum::values()],
            ['role', 'in', 'range' => MemberEnum::values()],
        ];
    }

    public function getFullName()
    {
        $email = $this->email;

        $contactQuery = new Query();
        $contactQuery->where(['email' => $email]);

        try {
            /** @var PersonalEntity $contactEntity */
            $contactEntity = App::$domain->contact->personal->one($contactQuery);
            return $contactEntity->getFullName();

        } catch (NotFoundHttpException $e) {
            $boxQuery = new Query();
            $boxQuery->where(['email' => $email]);
            $boxQuery->with('person');

            try {
                /** @var BoxEntity $boxEntity */
                $boxEntity = App::$domain->mail->repositories->box->one($boxQuery);
                return $boxEntity->person->full_name;

            } catch (NotFoundHttpException $e) {
                return null;
            }
        }
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['full_name'] = 'full_name';
        return $fields;
    }
}
