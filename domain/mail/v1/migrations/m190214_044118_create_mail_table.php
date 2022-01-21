<?php

use yii2lab\db\domain\db\MigrationCreateTable as Migration;

/**
 * Class m190214_044118_create_mail_table
 *
 * @package
 */
class m190214_044118_create_mail_table extends Migration
{

    public $table = 'mail_mail';

	/**
	 * @inheritdoc
	 */
	public function getColumns()
	{
		return [
			'id' => $this->primaryKey()->notNull()->comment('ID письма'),
            'ext_id' => $this->string()->comment('Внешний ID'),
			'kind' => $this->string()->notNull()->comment('Входящее или исходящее письмо'),
			'reply_from' => $this->string(64)->comment('Почтовый ящик с которого переслано письмо'),
			'from' => $this->string(64)->notNull()->comment('Отправитель'),
			'to' => $this->json()->notNull()->comment('Получатели'),
			'copy_to' => $this->json()->comment('Копия'),
            'reply_to' => $this->json()->comment('Переслано'),
            'blind_copy' => $this->json()->comment('"Слепая" копия'),
			'subject' => $this->string(254)->comment('Тема письма'),
			'content' => $this->text(),
            'status' => $this->integer()->notNull()->defaultValue(1)->comment('Статус'),

            'discussion_id' => $this->integer()->null()->comment('ID дискуссии'),
            'type' => $this->string()->notNull()->comment('Тип сообщения '),
            'is_draft' => $this->boolean()->notNull()->defaultValue(true)->comment('Флаг черновик'),

            //'sent_at' => $this->timestamp()->notNull()->comment('Дата отправки'),
            'created_at' => $this->timestamp()->notNull()->comment('Дата создания'),
            'updated_at' => $this->timestamp()->comment('Дата обновления'),
        ];
    }

    public function afterCreate()
    {

    }

}