<?php

use yii2lab\db\domain\db\MigrationCreateTable as Migration;

/**
 * Class m190214_044148_create_discussion_table
 *
 * @package
 */
class m190214_044148_create_discussion_table extends Migration
{

    public $table = 'mail_discussion';

    /**
     * @inheritdoc
     */
    public function getColumns()
    {
        return [
            'id' => $this->primaryKey()->notNull()->comment('Идентификатор'),
            'corporate_client_id' => $this->integer()->null()->comment('Ссылка на клиента'),
            'subject' => $this->string()->comment('Тема беседы'),
            'description' => $this->text()->null()->comment('Описание'),
            //'members' => $this->text()->notNull()->comment('Участники беседы с ролями'),
            'status' => $this->integer()->notNull()->defaultValue(1)->comment('Статус'),
            'created_at' => $this->timestamp()->notNull()->comment('Дата создания'),
            'updated_at' => $this->timestamp()->comment('Дата обновления'),
        ];
    }

    public function afterCreate()
    {

    }

}