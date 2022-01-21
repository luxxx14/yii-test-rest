<?php

use yii2lab\db\domain\db\MigrationCreateTable as Migration;

/**
 * Class m190214_044133_create_box_table
 *
 * @package
 */
class m190214_044133_create_box_table extends Migration
{

    public $table = 'mail_box';

    /**
     * @inheritdoc
     */
    public function getColumns()
    {
        return [
            'id' => $this->primaryKey()->notNull()->comment('Идентификатор'),
            'domain_id' => $this->integer()->notNull()->comment('Ссылка на домен'),
            'person_id' => $this->integer()->notNull()->comment('Ссылка на персону'),
            'quote_size' => $this->integer()->notNull()->defaultValue(8388608)->comment('Лимит дискового пространства'),
            'email' => $this->string(64)->notNull()->comment('Почтовый ящик'),
            'quota_size' => $this->bigInteger()->notNull()->defaultValue(8388608)->comment('Квота на ящик'), // 8 388 608 - 100 Мб в байтах.
            'status' => $this->integer()->notNull()->defaultValue(1)->comment('Статус'),
            'created_at' => $this->timestamp()->notNull()->comment('Дата создания'),
            'updated_at' => $this->timestamp()->comment('Дата обновления'),
        ];
    }

    public function afterCreate()
    {
        $this->myCreateIndexUnique(['email']);
        $this->myAddForeignKey(
            'domain_id',
            'mail_domain',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->myAddForeignKey(
            'person_id',
            'user_person',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

}