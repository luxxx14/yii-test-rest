<?php

use yii2lab\db\domain\db\MigrationAddColumn as Migration;

/**
 * Handles adding new_message_count to table `{{%discussion}}`.
 */
class m190527_114655_add_new_message_count_column_to_discussion_table extends Migration
{
    public  $table = 'mail.discussion';

    public function getColumns()
    {
        return [
            'new_message_count' => $this->integer(10)->defaultValue(0)->after('status'),
        ];
    }
}
