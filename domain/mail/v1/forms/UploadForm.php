<?php

namespace domain\mail\v1\forms;

use Yii;
use yii\web\UploadedFile;
use yii2rails\domain\base\Model;

class UploadForm extends Model
{

    public $file;
    public $mail_id;

    public function init()
    {
        if (Yii::$app->request->isPost) {
            $this->file = UploadedFile::getInstanceByName('file');
        }
    }

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false/*, 'extensions' => 'png, jpg'*/],
            [['mail_id'], 'required'],
            [['mail_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => Yii::t('main', 'file'),
        ];
    }
}
