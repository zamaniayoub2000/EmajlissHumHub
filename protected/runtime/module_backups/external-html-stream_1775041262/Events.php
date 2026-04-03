<?php

namespace humhub\modules\externalHtmlStream;

use Yii;
use humhub\modules\externalHtmlStream\jobs\MajlissSyncJob;
use humhub\modules\externalHtmlStream\jobs\RefreshExternalPostsJob;

/**
 * Gestionnaire d'événements du module External HTML Stream.
 */
class Events
{
    /**
     * Planifie la synchronisation automatique via le cron HumHub.
     *
     * Déclenché par EVENT_ON_HOURLY_RUN.
     * Lance les 2 jobs :
     *  - MajlissSyncJob    : synchronisation des posts WP Majliss
     *  - RefreshExternalPostsJob : rafraîchissement des contenus API externes
     *
     * @param \yii\base\Event $event
     */
    public static function onCronRun($event)
    {
        $module = Yii::$app->getModule('external-html-stream');

        // Sync Majliss si activé
        if ($module->getSetting('autoSyncEnabled', true)) {
            Yii::$app->queue->push(new MajlissSyncJob());
            Yii::info('MajlissSyncJob planifié via cron.', 'external-html-stream');
        }

        // Rafraîchir les contenus API externes
        Yii::$app->queue->push(new RefreshExternalPostsJob());
        Yii::info('RefreshExternalPostsJob planifié via cron.', 'external-html-stream');
    }
}
