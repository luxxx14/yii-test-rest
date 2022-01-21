<?php

use yii2lab\db\domain\db\MigrationCreateTable as Migration;

/**
 * Class m190217_073327_create_main.contacts_table
 * 
 * @package 
 */
class m190217_073327_create_contacts_table extends Migration {

	public $table = 'contact_personal';

	/**
	 * @inheritdoc
	 */
	public function getColumns()
	{
		return [
			'id' => $this->primaryKey()->notNull(),
			'person_id' => $this->integer()->notNull(),
			'first_name' => $this->string(128)->notNull(),
            'last_name' => $this->string(128),
            'middle_name' => $this->string(128),
			'phone' => $this->string(15),
            'kind' => $this->string(15),
			'email' => $this->string(64),
			'status' => $this->integer()->notNull()->defaultValue(1),
			'created_at' => $this->timestamp(),
			'updated_at' => $this->timestamp(),
		];
	}

	public function afterCreate()
	{
		$this->myAddForeignKey(
			'person_id',
			'user_person',
			'id',
			'CASCADE',
			'CASCADE'
		);
	}

}