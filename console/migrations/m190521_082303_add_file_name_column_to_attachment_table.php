<?php

use yii2lab\db\domain\db\MigrationAddColumn as Migration;

/**
 * Handles adding file_name to table `{{%attachment}}`.
 */
class m190521_082303_add_file_name_column_to_attachment_table extends Migration
{
    public  $table = 'mail.attachment';

    public function getColumns()
    {
        return [
            'file_name' => $this->char(255)->after('path'),
        ];
    }
}
