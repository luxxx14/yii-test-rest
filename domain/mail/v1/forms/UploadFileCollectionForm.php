<?php

namespace domain\mail\v1\forms;

use Yii;
use yii\web\UploadedFile;

class UploadFileCollectionForm extends UploadForm
{

    public $files = [];
    public $mail_id;

    public function init()
    {
        if (Yii::$app->request->isPost) {
            $this->files = UploadedFile::getInstancesByName('files');
        }
    }

    public function rules()
    {
        return [
            [['files'], 'required'],
            ['files', 'each', 'rule' => ['file', 'skipOnEmpty' => false]],
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
