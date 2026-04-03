<?php

use humhub\components\Migration;

/**
 * Handles adding columns to table `{{%homepage}}`.
 */
class m240517_162530_add_title_column_to_homepage_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn('{{%homepage}}', 'title', $this->string(255)->after('group_priority_order'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240517_162530_add_title_column_to_homepage_table cannot be reverted.\n";
    }
}
