<?php

use yii2lab\db\domain\db\MigrationCreateTable as Migration;

class m190326_044138_create_settings_table extends Migration
{

    public $table = 'mail_settings';

    /**
     * @inheritdoc
     */
    public function getColumns()
    {
        return [
            'person_id' => $this->integer()->notNull()->comment('ID персоны'),
            'language' => $this->string(5)->notNull()->comment('Язык интерфейса'),
            'sign_text' => $this->text()->notNull()->comment('Текст подписи к письму'),
            'redirect_emails' => $this->json()->null()->comment('Флаг пересылки письма'),
            'auto_answer_text' => $this->text()->null()->comment('Текст автоответа'),
            'is_enable_message_notify' => $this->integer()->notNull()->comment('Флаг включения уведомлений о сообщениях'),
            'is_enable_mail_notify' => $this->integer()->notNull()->comment('Флаг включения уведомлений о письмах'),
            'is_enable_sign' => $this->integer()->notNull()->comment('Флаг отображения подписи к письму'),
            'is_enable_redirect' => $this->integer()->notNull()->defaultValue(0)->comment('Флаг пересылки письма'),
            'is_enable_auto_answer' => $this->integer()->notNull()->defaultValue(0)->comment('Флаг автоответа'),
        ];
    }

    public function afterCreate()
    {
        $this->myCreateIndexUnique(['person_id']);
        $this->myAddForeignKey(
            'person_id',
            'user_person',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->myAddForeignKey(
            'language',
            'language',
            'code',
            'CASCADE',
            'CASCADE'
        );
    }

}