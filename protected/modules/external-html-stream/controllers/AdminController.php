<?php

namespace humhub\modules\externalHtmlStream\controllers;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\externalHtmlStream\models\ExternalPost;
use humhub\modules\externalHtmlStream\models\MajlissPost;
use humhub\modules\externalHtmlStream\models\ConfigForm;
use humhub\modules\externalHtmlStream\models\SyncLog;
use humhub\modules\externalHtmlStream\services\MajlissSyncService;
use humhub\modules\space\models\Space;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * AdminController — Backoffice complet :
 *  - CRUD publications HTML externes (ExternalPost)
 *  - Gestion des posts Majliss synchronisés via API REST WordPress
 *  - Déclenchement manuel de la synchronisation
 *  - Configuration du module (API WordPress)
 *  - Consultation des logs
 */
class AdminController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    //  MAJLISS — Gestion des posts synchronisés
    // ═══════════════════════════════════════════════════════════

    /**
     * Dashboard principal : liste des posts Majliss synchronisés.
     */
    public function actionIndex()
    {
        $majlissPosts = MajlissPost::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $externalPosts = ExternalPost::find()
            ->orderBy(['updated_at' => SORT_DESC])
            ->all();

        // Stats rapides
        $stats = [
            'total'   => MajlissPost::find()->count(),
            'success' => MajlissPost::find()->where(['sync_status' => MajlissPost::SYNC_SUCCESS])->count(),
            'errors'  => MajlissPost::find()->where(['sync_status' => MajlissPost::SYNC_ERROR])->count(),
            'pending' => MajlissPost::find()->where(['sync_status' => MajlissPost::SYNC_PENDING])->count(),
        ];

        return $this->render('index', [
            'majlissPosts'  => $majlissPosts,
            'externalPosts' => $externalPosts,
            'stats'         => $stats,
        ]);
    }

    /**
     * Lance manuellement la synchronisation Majliss → HumHub via API REST WP.
     */
    public function actionSync()
    {
        try {
            $service = new MajlissSyncService();
            $result  = $service->sync();

            Yii::$app->session->setFlash('success',
                Yii::t('ExternalHtmlStreamModule.base',
                    'Synchronisation terminée (via API REST). {success} succès, {errors} erreur(s) sur {total} post(s).',
                    $result
                )
            );
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('danger',
                Yii::t('ExternalHtmlStreamModule.base',
                    'Erreur de synchronisation : {error}',
                    ['error' => $e->getMessage()]
                )
            );
        }

        return $this->redirect(['index']);
    }

    /**
     * Teste la connexion à l'API REST WordPress (AJAX).
     */
    public function actionTestConnection()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $service = new MajlissSyncService();
            return $service->testConnection();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retente la synchronisation d'un post en erreur.
     * Récupère le post spécifique via GET /wp-json/wp/v2/posts/{wp_id}
     */
    public function actionRetrySync($id)
    {
        try {
            $service = new MajlissSyncService();
            $success = $service->retrySync((int) $id);

            if ($success) {
                Yii::$app->session->setFlash('success', 'Post resynchronisé avec succès via l\'API.');
            } else {
                Yii::$app->session->setFlash('warning', 'Impossible de resynchroniser ce post.');
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Publie un post en attente (le marque comme succès).
     */
    public function actionPublishPost($id)
    {
        $model = MajlissPost::findOne($id);
        if ($model && $model->sync_status === MajlissPost::SYNC_PENDING) {
            $model->markSuccess();
            Yii::$app->session->setFlash('success', 'Post publié avec succès.');
        }
        return $this->redirect(['index']);
    }

    /**
     * Publie tous les posts en attente.
     */
    public function actionPublishAllPending()
    {
        $posts = MajlissPost::find()
            ->where(['sync_status' => MajlissPost::SYNC_PENDING])
            ->all();

        $count = 0;
        foreach ($posts as $post) {
            $post->markSuccess();
            $count++;
        }

        Yii::$app->session->setFlash('success', "$count post(s) publié(s) avec succès.");
        return $this->redirect(['index']);
    }

    /**
     * Supprime tous les posts en attente.
     */
    public function actionDeleteAllPending()
    {
        $posts = MajlissPost::find()
            ->where(['sync_status' => MajlissPost::SYNC_PENDING])
            ->all();

        $count = 0;
        foreach ($posts as $post) {
            $post->hardDelete();
            $count++;
        }

        Yii::$app->session->setFlash('success', "$count post(s) en attente supprimé(s).");
        return $this->redirect(['index']);
    }

    /**
     * Supprime un post Majliss synchronisé.
     */
    public function actionDeleteMajliss($id)
    {
        $model = MajlissPost::findOne($id);
        if ($model) {
            $model->delete();
            $this->view->saved();
        }
        return $this->redirect(['index']);
    }

    // ═══════════════════════════════════════════════════════════
    //  EXTERNAL POSTS — CRUD publications HTML API
    // ═══════════════════════════════════════════════════════════

    /**
     * Création d'une nouvelle publication HTML externe.
     */
    public function actionCreate()
    {
        $model = new ExternalPost();
        $model->refresh_interval = 3600;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->space_id) {
                $space = Space::findOne($model->space_id);
                if ($space) {
                    $model->content->container  = $space;
                    $model->content->visibility = \humhub\modules\content\models\Content::VISIBILITY_PUBLIC;
                }
            }

            if ($model->validate() && $model->save()) {
                $this->view->saved();
                return $this->redirect(['index']);
            }
        }

        $spaces = Space::find()->orderBy('name')->all();

        return $this->render('create', [
            'model'  => $model,
            'spaces' => $spaces,
        ]);
    }

    /**
     * Mise à jour d'une publication HTML externe.
     */
    public function actionUpdate($id)
    {
        $model = $this->findExternalPost($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
            return $this->redirect(['index']);
        }

        $spaces = Space::find()->orderBy('name')->all();

        return $this->render('update', [
            'model'  => $model,
            'spaces' => $spaces,
        ]);
    }

    /**
     * Suppression d'une publication HTML externe.
     */
    public function actionDelete($id)
    {
        $model = $this->findExternalPost($id);
        $model->delete();

        $this->view->saved();
        return $this->redirect(['index']);
    }

    /**
     * Teste l'API d'une publication (AJAX).
     */
    public function actionTestApi($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findExternalPost($id);
        $success = $model->fetchContent();

        return [
            'success'        => $success,
            'message'        => $success
                ? 'API testée avec succès. Contenu récupéré.'
                : 'Erreur lors de l\'appel API. Vérifiez l\'URL et les logs.',
            'html_preview'   => $success ? mb_substr(strip_tags($model->cached_html), 0, 200) . '...' : '',
            'last_fetched_at' => $model->last_fetched_at,
        ];
    }

    /**
     * Rafraîchit manuellement le contenu d'une publication.
     */
    public function actionRefresh($id)
    {
        $model = $this->findExternalPost($id);
        $model->fetchContent();

        $this->view->saved();
        return $this->redirect(['index']);
    }

    /**
     * Rafraîchit toutes les publications qui en ont besoin.
     */
    public function actionRefreshAll()
    {
        $posts = ExternalPost::findNeedingRefresh();
        $count = 0;

        foreach ($posts as $post) {
            if ($post->fetchContent()) {
                $count++;
            }
        }

        Yii::$app->session->setFlash('success',
            "$count publication(s) rafraîchie(s)."
        );

        return $this->redirect(['index']);
    }

    // ═══════════════════════════════════════════════════════════
    //  CONFIGURATION
    // ═══════════════════════════════════════════════════════════

    /**
     * Page de configuration globale du module.
     */
    public function actionConfig()
    {
        $form = new ConfigForm();
        $form->loadSettings();

        if ($form->load(Yii::$app->request->post()) && $form->saveSettings()) {
            $this->view->saved();
            return $this->redirect(['config']);
        }

        $spaces = Space::find()->orderBy('name')->all();

        return $this->render('config', [
            'model'  => $form,
            'spaces' => $spaces,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    //  LOGS
    // ═══════════════════════════════════════════════════════════

    /**
     * Affiche les logs de synchronisation.
     */
    public function actionLogs()
    {
        $logs = SyncLog::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(200)
            ->all();

        return $this->render('logs', [
            'logs' => $logs,
        ]);
    }

    /**
     * Nettoie les anciens logs (AJAX).
     */
    public function actionClearLogs()
    {
        $deleted = SyncLog::cleanup(30);
        Yii::$app->session->setFlash('success', "$deleted log(s) supprimé(s).");
        return $this->redirect(['logs']);
    }

    // ═══════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════

    /**
     * Trouve un ExternalPost par ID.
     */
    protected function findExternalPost($id): ExternalPost
    {
        $model = ExternalPost::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Publication introuvable.');
        }
        return $model;
    }
}
