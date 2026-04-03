<?php

use humhub\components\Migration;

/**
 * Handles adding columns to table `{{%homepage}}`.
 */
class m250528_112440_add_no_frame_column_to_homepage_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeAddColumn('{{%homepage}}', 'no_frame', $this->boolean()->notNull()->defaultValue(false)->after('widgets'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250528_112440_add_no_frame_column_to_homepage_table cannot be reverted.\n";
    }
}
