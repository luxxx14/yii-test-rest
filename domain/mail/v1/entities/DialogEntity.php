<?php

namespace domain\mail\v1\entities;

use App;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii2rails\domain\data\Query;
use yubundle\staff\domain\v1\entities\WorkerEntity;
use yii2rails\domain\BaseEntity;
use yii2rails\domain\behaviors\entity\TimeValueFilter;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;
use yubundle\user\domain\v1\entities\PersonEntity;

/**
 * Class DialogEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $id
 * @property $actor
 * @property $contractor
 * @property $created_at
 * @property $updated_at
 * @property BoxEntity $box
 * @property $status
 * @property $new_message_count
 * @property WorkerEntity $worker
 */
class DialogEntity extends BaseEntity
{

    protected $id;
    protected $actor;
    protected $contractor;
    protected $created_at;
    protected $updated_at;
    protected $status = StatusEnum::ENABLE;
    protected $worker;
    protected $box;
    protected $last_message_content = 'Последнее сообщение (мок)';
    protected $new_message_count = 0;
    protected $flagged = false;
    protected $mails;

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
            'status' => 'integer',
            'created_at' => TimeValue::class,
            'updated_at' => TimeValue::class,
            'worker' => WorkerEntity::class,
            'person' => PersonEntity::class
        ];
    }

    public function rules()
    {
        return [
            [['actor', 'contractor'], 'trim'],
            [['actor', 'contractor'], 'email'],
            [['actor', 'contractor'], 'required'],
            ['status', 'in', 'range' => StatusEnum::values()],
        ];
    }

    public function getIsCorparatePerson()
    {
        return ArrayHelper::getValue($this, 'box.person.worker') != null;
    }

    public function getFullName()
    {
        $person = ArrayHelper::getValue($this, 'box.person');
        if ($person == null) {
            return null;
        }
        return $person->getFullName();
    }

    public function getPostName()
    {
        return ArrayHelper::getValue($this, 'box.person.worker.post.value');
    }

    public function getDivisionName()
    {
        return ArrayHelper::getValue($this, 'box.person.worker.division.name');
    }

    public function getLastMessage()
    {
        $query = new Query();
        $query->andWhere(['dialog_id' => $this->id, 'status' => StatusEnum::ENABLE, 'mail_address' => $this->actor]);
        $query->orderBy(['created_at' => SORT_DESC]);
        $query->limit(1);
        $query->with('mail');
        try {
            /** @var MailEntity $mailEntity */
            $flowEntity = App::$domain->mail->flow->repository->one($query);
            return $flowEntity->getShortContent();
        } catch (NotFoundHttpException $e) {
            return null;
        }
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['full_name'] = 'full_name';
        $fields['post_name'] = 'post_name';
        $fields['division_name'] = 'division_name';
        $fields['last_message_content'] = 'last_message';
        $fields['is_corparate_person'] = 'is_corparate_person';
        //unset($fields['actor']);
        unset($fields['box']);
        unset($fields['mails']);
        unset($fields['worker']);
        return $fields;
    }
}
