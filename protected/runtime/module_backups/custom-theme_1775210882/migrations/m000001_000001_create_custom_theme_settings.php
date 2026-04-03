<?php

use yii\db\Migration;

/**
 * Migration pour créer la table custom_theme_settings
 * Stocke les personnalisations du thème (header, footer, CSS, JS)
 */
class m000001_000001_create_custom_theme_settings extends Migration
{
    public function safeUp()
    {
        $this->createTable('custom_theme_settings', [
            'id' => $this->primaryKey(),
            'setting_key' => $this->string(100)->notNull()->unique(),
            'setting_value' => $this->text(),
            'is_active' => $this->boolean()->defaultValue(true),
            'created_at' => $this->dateTime()->defaultExpression('NOW()'),
            'updated_at' => $this->dateTime()->defaultExpression('NOW()'),
        ]);

        // Insérer les paramètres par défaut
        $this->batchInsert('custom_theme_settings', ['setting_key', 'setting_value', 'is_active'], [
            ['footer_html', '', 1],
            ['header_html', '', 1],
            ['custom_css', '', 1],
            ['custom_js', '', 1],
            ['footer_active', '1', 1],
            ['header_active', '0', 1],
            ['css_active', '1', 1],
            ['js_active', '1', 1],
            ['sanitize_html', '0', 1],
        ]);

        return true;
    }

    public function safeDown()
    {
        $this->dropTable('custom_theme_settings');
        return true;
    }
}
