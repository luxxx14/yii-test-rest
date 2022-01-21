<?php

use yii2lab\db\domain\db\MigrationCreateTable as Migration;

/**
 * Class m190214_044221_create_flow_table
 *
 * @package
 */
class m190214_044221_create_flow_table extends Migration
{

    public $table = 'mail_flow';

    /**
     * @inheritdoc
     */
    public function getColumns()
    {
        return [
            'id' => $this->primaryKey()->notNull()->comment('Идентификатор'),
            'direct' => $this->string(16)->notNull()->comment('Направление письма'),
            'mail_id' => $this->integer()->notNull()->comment('Письмо'),
            'dialog_id' => $this->integer()->null()->comment('Диалог'),
            'mail_address' => $this->string(128)->notNull(),

            'folder' => $this->string()->null()->comment('Папка'),
            'has_attachment' => $this->boolean()->defaultValue(false)->comment('Есть ли вложения'),

            'seen' => $this->boolean()->defaultValue(false),
            'flagged' => $this->boolean()->defaultValue(false),
            //'draft' => $this->boolean()->defaultValue(false),
            //'spam' => $this->boolean()->defaultValue(false),
            'status' => $this->integer()->notNull()->defaultValue(1)->comment('Статус'),
            //'date' => $this->timestamp()->comment('Дата прочтения'),

            'created_at' => $this->timestamp()->notNull()->comment('Дата создания'),
            'updated_at' => $this->timestamp()->comment('Дата обновления'),
        ];
    }

    public function afterCreate()
    {
        $this->myAddForeignKey(
            'mail_id',
            'mail_mail',
            'id',
            'CASCADE',
            'CASCADE'
        );
        /*$this->myAddForeignKey(
            'dialog_id',
            'mail_dialog',
            'id',
            'CASCADE',
            'CASCADE'
        );*/
    }

}