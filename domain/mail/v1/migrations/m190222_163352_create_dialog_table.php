<?php

use yii2lab\db\domain\db\MigrationCreateTable as Migration;

/**
 * Class m190222_163352_create_dialog_table
 *
 * @package
 */
class m190222_163352_create_dialog_table extends Migration
{

    public $table = 'mail_dialog';

    /**
     * @inheritdoc
     */
    public function getColumns()
    {
        return [
            'id' => $this->primaryKey()->notNull()->comment('Идентификатор'),
            'actor' => $this->string(128)->notNull()->comment('Отправитель'),
            'contractor' => $this->string(128)->notNull()->comment('Получатель'),
            'status' => $this->integer()->notNull()->defaultValue(1)->comment('Статус'),
            'new_message_count' => $this->integer()->defaultValue(0)->notNull()->comment('Количество новых сообщений'),
            'flagged' => $this->boolean()->notNull()->defaultValue(false)->comment('Флаг избранное'),
            'created_at' => $this->timestamp()->notNull()->comment('Дата создания'),
            'updated_at' => $this->timestamp()->comment('Дата обновления'),
        ];
    }

    public function afterCreate()
    {

    }

}