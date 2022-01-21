<?php

namespace domain\mail\v1\entities;

use yii2rails\app\domain\helpers\EnvService;
use yii2rails\domain\BaseEntity;
use yii2rails\domain\behaviors\entity\TimeValueFilter;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;
use yii2rails\extension\yii\helpers\FileHelper;

/**
 * Class AttachmentEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $id
 * @property $mail_id
 * @property $path
 * @property $extension
 * @property $size
 * @property $status
 * @property $created_at
 * @property $updated_at
 * @property $file_name
 *
 * @property-read MailEntity $mail
 */
class AttachmentEntity extends BaseEntity
{

    protected $id;
    protected $mail_id;
    protected $path;
    protected $extension;
    protected $size;
    protected $status = StatusEnum::ENABLE;
    protected $created_at;
    protected $updated_at;
    protected $file_name;

    protected $mail;

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
            'mail_id' => 'integer',
            'path' => 'string',
            'file_name' => 'string',
            'extension' => 'string',
            'size' => 'integer',
            'status' => 'integer',
            'created_at' => TimeValue::class,
            'updated_at' => TimeValue::class,
        ];
    }

    public function rules()
    {
        return [
            ['status', 'in', 'range' => StatusEnum::values()],
        ];
    }

    public function getUrl()
    {
        return EnvService::getStaticUrl($this->path);
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['url'] = 'url';
        return $fields;
    }
}
