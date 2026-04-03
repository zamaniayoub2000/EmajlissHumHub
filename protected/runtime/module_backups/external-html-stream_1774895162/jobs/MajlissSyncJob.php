<?php

namespace humhub\modules\externalHtmlStream\jobs;

use Yii;
use humhub\modules\queue\ActiveJob;
use humhub\modules\externalHtmlStream\services\MajlissSyncService;
use humhub\modules\externalHtmlStream\models\SyncLog;

/**
 * Job de synchronisation Majliss → HumHub.
 *
 * Exécuté via le système de queue HumHub,
 * déclenché par le cron horaire ou manuellement.
 */
class MajlissSyncJob extends ActiveJob
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        try {
            $service = new MajlissSyncService();
            $result  = $service->sync();

            Yii::info(
                "MajlissSyncJob: {$result['success']} succès, {$result['errors']} erreur(s).",
                'external-html-stream'
            );

        } catch (\Exception $e) {
            SyncLog::error("MajlissSyncJob échoué : " . $e->getMessage());
            Yii::error("MajlissSyncJob: " . $e->getMessage(), 'external-html-stream');
        }
    }
}
