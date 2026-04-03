<?php

use humhub\components\Migration;

/**
 * Class m231231_110809_initial
 */
class m231231_110809_initial extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->safeCreateTable('homepage', [
            'id' => $this->primaryKey(),
            'enabled' => $this->boolean()->notNull()->defaultValue(false),
            'target' => $this->string(100)->notNull(),
            'group_id' => $this->integer(11),
            'group_priority_order' => $this->integer(11),
            'content' => $this->text(),
            'content_type' => $this->string(100)->notNull(),
            'widgets' => $this->text(),
            'layout' => $this->string(100)->notNull(),
            'created_at' => $this->dateTime(),
            'created_by' => $this->integer(11),
            'updated_at' => $this->dateTime(),
            'updated_by' => $this->integer(11),
        ]);

        $this->safeAddForeignKey('fk-homepage-group', 'homepage', 'group_id', 'group', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231231_110809_initial cannot be reverted.\n";

        return false;
    }
}
