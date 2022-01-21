<?php

use yii2lab\db\domain\db\MigrationCreateTable as Migration;

/**
 * Class m190214_044211_create_discussions_mail_table
 *
 * @package
 */
class m190214_044211_create_discussions_mail_table extends Migration
{

    public $table = 'mail_discussion_mail';

    /**
     * @inheritdoc
     */
    public function getColumns()
    {
        return [
            'id' => $this->primaryKey()->notNull()->comment('Идентификатор'),
            'mail_id' => $this->integer()->notNull()->comment('Ссылка на письмо'),
            'discussion_id' => $this->integer()->notNull()->comment('Ссылка на дискуссии'),
            'status' => $this->integer()->notNull()->defaultValue(1)->comment('Статус'),
            'created_at' => $this->timestamp()->notNull()->comment('Дата создания'),
            'updated_at' => $this->timestamp()->comment('Дата обновления'),
        ];
    }

    public function afterCreate()
    {
        $this->myAddForeignKey(
            'discussion_id',
            'mail_discussion',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->myAddForeignKey(
            'mail_id',
            'mail_mail',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

}