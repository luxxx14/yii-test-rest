<?php

use yii2lab\db\domain\db\MigrationCreateTable as Migration;

/**
 * Class m190214_044132_create_domain_table
 *
 * @package
 */
class m190214_044132_create_domain_table extends Migration
{

    public $table = 'mail_domain';

    /**
     * @inheritdoc
     */
    public function getColumns()
    {
        return [
            'id' => $this->primaryKey()->notNull()->comment('Идентификатор'),
            'company_id' => $this->integer()->notNull()->comment('Компания'),
            'domain' => $this->string(32)->notNull()->comment('Наименование сервиса'),
            'host' => $this->string(64)->notNull()->comment('IP почтового сервера'),
            'port' => $this->integer()->notNull()->comment('Порт сервера'),
            //'kind' => $this->string()->notNull()->comment('Портальный или корпоративный клиент'),
            'status' => $this->integer()->notNull()->defaultValue(1)->comment('Статус'),
            'created_at' => $this->timestamp()->notNull()->comment('Дата создания'),
            'updated_at' => $this->timestamp()->comment('Дата обновления'),
        ];
    }

    public function afterCreate()
    {
        $this->myAddForeignKey(
            'company_id',
            'company',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

}