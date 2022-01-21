<?php

use yii2lab\db\domain\db\MigrationCreateTable as Migration;

/**
 * Class m190222_140732_create_mail.discussion_member_table
 *
 * @package
 */
class m190222_140732_create_discussion_member_table extends Migration
{

    public $table = 'mail_discussion_member';

    /**
     * @inheritdoc
     */
    public function getColumns()
    {
        return [
            'id' => $this->primaryKey()->notNull()->comment('Идентификатор'),
            'discussion_id' => $this->integer()->notNull()->comment('Ссылка на дискуссию'),
            'email' => $this->string(128)->notNull()->comment('Участник'),
            'new_message_count' => $this->integer()->notNull()->defaultValue(0)->comment('Колличество новых сообщений'),
            'role' => $this->string()->notNull()->comment('Роль'),
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
    }

}