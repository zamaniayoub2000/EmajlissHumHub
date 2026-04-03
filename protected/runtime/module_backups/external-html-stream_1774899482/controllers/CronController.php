<?php

namespace humhub\modules\externalHtmlStream\controllers;

use Yii;
use yii\console\Controller;
use humhub\modules\externalHtmlStream\models\ExternalPost;
use humhub\modules\externalHtmlStream\services\MajlissSyncService;

/**
 * CronController — Commandes console pour la synchronisation et le rafraîchissement.
 *
 * Usage :
 *   php yii external-html-stream/cron/sync              ← Sync Majliss via API REST WP
 *   php yii external-html-stream/cron/refresh            ← Refresh ExternalPost
 *   php yii external-html-stream/cron/refresh-post --id=5
 *   php yii external-html-stream/cron/test-connection    ← Test connexion API WP
 *
 * Cron recommandé :
 *   0 * * * * cd /path/to/humhub && php yii external-html-stream/cron/sync
 */
class CronController extends Controller
{
    /**
     * Lance la synchronisation Majliss → HumHub via API REST WordPress.
     */
    public function actionSync()
    {
        $this->stdout("=== Synchronisation Majliss → HumHub (via API REST) ===\n");

        try {
            $service = new MajlissSyncService();
            $result  = $service->sync();

            $this->stdout("\n");
            $this->stdout("Total     : {$result['total']}\n");
            $this->stdout("Succès    : {$result['success']}\n");
            $this->stdout("Erreurs   : {$result['errors']}\n");
            $this->stdout("=== Terminé ===\n");

            return self::EXIT_CODE_NORMAL;

        } catch (\Exception $e) {
            $this->stderr("ERREUR FATALE : " . $e->getMessage() . "\n");
            return self::EXIT_CODE_ERROR;
        }
    }

    /**
     * Teste la connexion à l'API WordPress REST.
     *
     * Vérifie que :
     *  - L'URL est accessible
     *  - L'authentification fonctionne
     *  - L'API retourne des posts
     */
    public function actionTestConnection()
    {
        $this->stdout("Test de connexion API WordPress REST...\n");

        try {
            $module = Yii::$app->getModule('external-html-stream');
            $apiConfig = $module->getWpApiConfig();

            $this->stdout("  URL de base  : {$apiConfig['base_url']}\n");
            $this->stdout("  Endpoint     : {$apiConfig['endpoint']}\n");
            $this->stdout("  Auth méthode : {$apiConfig['auth_method']}\n");
            $this->stdout("  Timeout      : {$apiConfig['timeout']}s\n\n");

            $service = new MajlissSyncService();
            $result  = $service->testConnection();

            if ($result['success']) {
                $this->stdout("SUCCÈS : {$result['message']}\n");
                $this->stdout("Posts publiés dans WordPress : {$result['post_count']}\n");
                $this->stdout("Déjà synchronisés            : {$result['synced_count']}\n");
                $this->stdout("Restant à synchroniser       : {$result['remaining']}\n");
                return self::EXIT_CODE_NORMAL;
            }

            $this->stderr("ÉCHEC : {$result['message']}\n");
            return self::EXIT_CODE_ERROR;

        } catch (\Exception $e) {
            $this->stderr("ERREUR : " . $e->getMessage() . "\n");
            return self::EXIT_CODE_ERROR;
        }
    }

    /**
     * Rafraîchit les publications HTML externes (API).
     */
    public function actionRefresh()
    {
        $this->stdout("Rafraîchissement des publications externes...\n");

        $posts = ExternalPost::findNeedingRefresh();
        $success = 0;
        $failed = 0;

        foreach ($posts as $post) {
            $this->stdout("  [{$post->id}] \"{$post->title}\"... ");

            if ($post->fetchContent()) {
                $this->stdout("OK\n");
                $success++;
            } else {
                $this->stdout("ERREUR\n");
                $failed++;
            }
        }

        $this->stdout("\nTerminé : {$success} succès, {$failed} échec(s).\n");
        return self::EXIT_CODE_NORMAL;
    }

    /**
     * Rafraîchit une publication spécifique.
     */
    public function actionRefreshPost($id)
    {
        $post = ExternalPost::findOne($id);

        if ($post === null) {
            $this->stderr("Publication #{$id} introuvable.\n");
            return self::EXIT_CODE_ERROR;
        }

        $this->stdout("Rafraîchissement de \"{$post->title}\"... ");

        if ($post->fetchContent()) {
            $this->stdout("OK\n");
            return self::EXIT_CODE_NORMAL;
        }

        $this->stdout("ERREUR\n");
        return self::EXIT_CODE_ERROR;
    }
}
