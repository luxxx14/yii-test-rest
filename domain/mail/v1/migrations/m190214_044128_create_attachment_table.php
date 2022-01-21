<?php

use yii2lab\db\domain\db\MigrationCreateTable as Migration;

/**
 * Class m190214_044128_create_attachment_table
 *
 * @package
 */
class m190214_044128_create_attachment_table extends Migration
{

    public $table = 'mail_attachment';

    /**
     * @inheritdoc
     */
    public function getColumns()
    {
        return [
            'id' => $this->primaryKey()->notNull()->comment('Идентификатор'),
            'mail_id' => $this->integer()->notNull()->comment('Ссылка на письмо'),
            'path' => $this->string()->notNull()->comment('Абсолютный путь'),
            'extension' => $this->string(16)->notNull()->comment('Расширение файла'),
            'size' => $this->integer()->notNull()->comment('Размер файла'),
            'status' => $this->integer()->notNull()->defaultValue(1)->comment('Статус'),
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
    }

}