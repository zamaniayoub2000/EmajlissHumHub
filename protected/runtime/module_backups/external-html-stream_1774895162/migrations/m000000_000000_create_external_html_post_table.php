<?php

use yii\db\Migration;

/**
 * Migration : crée les tables du module external-html-stream.
 *
 * Tables :
 *  - external_html_post   : publications HTML externes (API générique)
 *  - majliss_synced_post  : posts WordPress Majliss synchronisés
 *  - majliss_sync_log     : journal des opérations de synchronisation
 *
 * Exécuter via : php yii migrate --migrationPath=@external-html-stream/migrations
 */
class m000000_000000_create_external_html_post_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // ── Table 1 : Publications HTML externes (API générique) ──
        $this->createTable('external_html_post', [
            'id'               => $this->primaryKey(),
            'title'            => $this->string(255)->notNull(),
            'api_url'          => $this->text()->notNull(),
            'refresh_interval' => $this->integer()->notNull()->defaultValue(3600),
            'last_fetched_at'  => $this->dateTime()->null(),
            'cached_html'      => $this->text()->null(),
            'space_id'         => $this->integer()->null(),
            'created_at'       => $this->dateTime()->notNull(),
            'updated_at'       => $this->dateTime()->notNull(),
        ]);

        $this->createIndex(
            'idx-external_html_post-space_id',
            'external_html_post',
            'space_id'
        );

        // ── Table 2 : Posts WordPress Majliss synchronisés ──
        $this->createTable('majliss_synced_post', [
            'id'              => $this->primaryKey(),
            'wp_post_id'      => $this->integer()->notNull()->unique(),
            'title'           => $this->string(255)->notNull(),
            'content'         => $this->text()->null(),
            'category'        => $this->string(255)->null(),
            'wp_date'         => $this->dateTime()->null(),
            'image_url'       => $this->string(1024)->null(),
            'image_file_guid' => $this->string(64)->null(),
            'space_id'        => $this->integer()->null(),
            'synced_at'       => $this->dateTime()->null(),
            'sync_status'     => $this->smallInteger()->notNull()->defaultValue(2),
            'sync_error'      => $this->text()->null(),
            'created_at'      => $this->dateTime()->notNull(),
            'updated_at'      => $this->dateTime()->notNull(),
        ]);

        $this->createIndex(
            'idx-majliss_synced_post-wp_post_id',
            'majliss_synced_post',
            'wp_post_id',
            true // unique
        );

        $this->createIndex(
            'idx-majliss_synced_post-space_id',
            'majliss_synced_post',
            'space_id'
        );

        $this->createIndex(
            'idx-majliss_synced_post-sync_status',
            'majliss_synced_post',
            'sync_status'
        );

        // ── Table 3 : Journal de synchronisation ──
        $this->createTable('majliss_sync_log', [
            'id'         => $this->primaryKey(),
            'level'      => $this->string(10)->notNull()->defaultValue('info'),
            'message'    => $this->text()->notNull(),
            'context'    => $this->text()->null(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex(
            'idx-majliss_sync_log-level',
            'majliss_sync_log',
            'level'
        );

        $this->createIndex(
            'idx-majliss_sync_log-created_at',
            'majliss_sync_log',
            'created_at'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('majliss_sync_log');
        $this->dropTable('majliss_synced_post');
        $this->dropTable('external_html_post');
    }
}
