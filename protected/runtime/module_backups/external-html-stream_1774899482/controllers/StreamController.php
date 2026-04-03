<?php

namespace humhub\modules\externalHtmlStream\controllers;

use Yii;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\externalHtmlStream\models\ExternalPost;
use humhub\modules\externalHtmlStream\models\MajlissPost;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * StreamController — Gère l'affichage et le rafraîchissement des contenus
 * dans le contexte d'un espace (content container).
 */
class StreamController extends ContentContainerController
{
    /**
     * Affiche un post externe.
     */
    public function actionView($id)
    {
        $model = ExternalPost::findOne($id);
        if (!$model) {
            $model = MajlissPost::findOne($id);
        }

        if ($model === null) {
            throw new NotFoundHttpException('Publication introuvable.');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Rafraîchit le contenu d'un post externe (AJAX).
     */
    public function actionRefresh($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = ExternalPost::findOne($id);
        if ($model === null) {
            return ['success' => false, 'html' => '', 'message' => 'Post introuvable'];
        }

        $success = $model->fetchContent();

        return [
            'success'        => $success,
            'html'           => $success ? $model->cached_html : '',
            'last_fetched_at' => $model->last_fetched_at,
        ];
    }
}
