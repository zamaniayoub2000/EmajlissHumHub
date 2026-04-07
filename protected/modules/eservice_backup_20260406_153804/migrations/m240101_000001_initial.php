<?php

use yii\db\Migration;

class m240101_000001_initial extends Migration
{
    public function safeUp()
    {
        // Main requests table
        $this->createTable('e_service_request', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'type' => $this->string(50)->notNull()->comment('hebergement, billet_avion, document, indemnite, support'),
            'sub_type' => $this->string(100)->null()->comment('For document requests: reservation, bulletin, dossier, documentation, proposition'),
            'status' => $this->string(20)->notNull()->defaultValue('pending')->comment('pending, in_progress, approved, rejected'),
            'event_name' => $this->string(255)->null(),
            'date_start' => $this->date()->null(),
            'date_end' => $this->date()->null(),
            'flight_plan' => $this->text()->null(),
            'shuttle_arrival' => $this->boolean()->defaultValue(false),
            'shuttle_departure' => $this->boolean()->defaultValue(false),
            'observations' => $this->text()->null(),
            'admin_comment' => $this->text()->null(),
            'extra_data' => $this->text()->null()->comment('JSON for dynamic form fields'),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_esr_user', 'e_service_request', 'user_id');
        $this->createIndex('idx_esr_type', 'e_service_request', 'type');
        $this->createIndex('idx_esr_status', 'e_service_request', 'status');
        $this->createIndex('idx_esr_created', 'e_service_request', 'created_at');
        $this->addForeignKey(
            'fk_esr_user',
            'e_service_request',
            'user_id',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Files table
        $this->createTable('e_service_file', [
            'id' => $this->primaryKey(),
            'request_id' => $this->integer()->notNull(),
            'filename' => $this->string(255)->notNull(),
            'original_name' => $this->string(255)->notNull(),
            'mime_type' => $this->string(100)->notNull(),
            'file_size' => $this->integer()->notNull(),
            'file_path' => $this->string(500)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_esf_request', 'e_service_file', 'request_id');
        $this->addForeignKey(
            'fk_esf_request',
            'e_service_file',
            'request_id',
            'e_service_request',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Status log / history table
        $this->createTable('e_service_status_log', [
            'id' => $this->primaryKey(),
            'request_id' => $this->integer()->notNull(),
            'old_status' => $this->string(20)->null(),
            'new_status' => $this->string(20)->notNull(),
            'comment' => $this->text()->null(),
            'changed_by' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_essl_request', 'e_service_status_log', 'request_id');
        $this->addForeignKey(
            'fk_essl_request',
            'e_service_status_log',
            'request_id',
            'e_service_request',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_essl_user',
            'e_service_status_log',
            'changed_by',
            'user',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Events reference table
        $this->createTable('e_service_event', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'is_active' => $this->boolean()->defaultValue(true),
            'sort_order' => $this->integer()->defaultValue(0),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Seed events from PowerApps
        $this->batchInsert('e_service_event', ['name', 'is_active', 'sort_order'], [
            ['Réunion de commission permanente', 1, 1],
            ['Réunion du Bureau', 1, 2],
            ['Assemblée Générale', 1, 3],
            ['Réunion : groupe de travail', 1, 4],
            ['Réunion : commission ADHOC', 1, 5],
            ['Autre', 1, 6],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('e_service_status_log');
        $this->dropTable('e_service_file');
        $this->dropTable('e_service_request');
        $this->dropTable('e_service_event');
    }
}
