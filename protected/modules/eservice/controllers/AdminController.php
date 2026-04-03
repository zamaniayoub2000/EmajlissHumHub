<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\eservice\controllers;

use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use humhub\components\Controller;
use humhub\modules\eservice\models\EServiceRequest;
use humhub\modules\eservice\models\EServiceRequestSearch;
use humhub\modules\eservice\models\EServiceStatusLog;

/**
 * AdminController handles administrative operations for E-Service requests.
 *
 * Provides actions for listing, viewing, updating status, and exporting
 * service requests. Access is restricted to platform administrators.
 *
 * @package humhub\modules\eservice\controllers
 */
class AdminController extends Controller
{
    /**
     * {@inheritdoc}
     *
     * Requires login for all actions (admin check done in beforeAction).
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * Checks that the current user is a system administrator.
     *
     * @throws ForbiddenHttpException if the user is not an admin
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Yii::$app->user->identity || !Yii::$app->user->identity->isSystemAdmin()) {
            throw new ForbiddenHttpException('Accès réservé aux administrateurs.');
        }

        return true;
    }

    /**
     * Displays the admin index page with a searchable/filterable list of all requests.
     *
     * Uses EServiceRequestSearch to apply filters from GET parameters and provides
     * a paginated data provider to the view.
     *
     * @return string the rendered view
     */
    public function actionIndex()
    {
        $searchModel = new EServiceRequestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays the detailed view of a specific E-Service request.
     *
     * Eager loads related files and status logs for display.
     *
     * @param int $id the request ID
     * @return string the rendered view
     * @throws NotFoundHttpException if the request is not found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Eager load relations
        $model->populateRelation('files', $model->getFiles()->all());
        $model->populateRelation('statusLogs', $model->getStatusLogs()->all());

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Updates the status of an E-Service request.
     *
     * Accepts POST data with 'new_status' and optional 'comment'. Creates a
     * status log entry, updates the request status and admin comment, then
     * redirects back to the request view with a flash message.
     *
     * @param int $id the request ID
     * @return \yii\web\Response a redirect response
     * @throws NotFoundHttpException if the request is not found
     * @throws ForbiddenHttpException if the request method is not POST
     */
    public function actionUpdateStatus($id)
    {
        $model = $this->findModel($id);
        $request = Yii::$app->request;

        if (!$request->isPost) {
            throw new ForbiddenHttpException(Yii::t('EserviceModule.base', 'Methode non autorisee.'));
        }

        $newStatus = $request->post('new_status');
        $comment = $request->post('comment');

        // Validate the new status
        if (!array_key_exists($newStatus, EServiceRequest::getStatusesList())) {
            Yii::$app->session->setFlash('error', Yii::t('EserviceModule.base', 'Statut invalide.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $oldStatus = $model->status;

        // Create status log entry
        $log = new EServiceStatusLog();
        $log->request_id = $model->id;
        $log->old_status = $oldStatus;
        $log->new_status = $newStatus;
        $log->comment = $comment;
        $log->changed_by = Yii::$app->user->id;

        if (!$log->save()) {
            Yii::error('Failed to save EServiceStatusLog: ' . json_encode($log->errors), 'eservice');
            Yii::$app->session->setFlash('error', Yii::t('EserviceModule.base', 'Erreur lors de la mise a jour du statut.'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Update request status and admin comment
        $model->status = $newStatus;
        if (!empty($comment)) {
            $model->admin_comment = $comment;
        }

        if ($model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('EserviceModule.base', 'Le statut a ete mis a jour avec succes.'));
        } else {
            Yii::error('Failed to update EServiceRequest: ' . json_encode($model->errors), 'eservice');
            Yii::$app->session->setFlash('error', Yii::t('EserviceModule.base', 'Erreur lors de la sauvegarde de la demande.'));
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Exports filtered E-Service requests to a CSV file.
     *
     * Applies the same search filters as the index action and generates a
     * downloadable CSV with proper headers and UTF-8 BOM for Excel compatibility.
     *
     * @return \yii\web\Response the CSV file download response
     */
    public function actionExport()
    {
        $searchModel = new EServiceRequestSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination = false; // Export all matching records

        $filename = 'eservice_export_' . date('Y-m-d_His') . '.csv';
        $tempFile = Yii::getAlias('@runtime') . '/' . $filename;

        $handle = fopen($tempFile, 'w');

        // UTF-8 BOM for proper Excel encoding
        fwrite($handle, "\xEF\xBB\xBF");

        // CSV header row
        fputcsv($handle, [
            'ID',
            'Utilisateur',
            'Type',
            'Sous-type',
            'Statut',
            'Manifestation',
            'Date de debut',
            'Date de fin',
            'Observations',
            'Commentaire administrateur',
            'Date de creation',
            'Date de modification',
        ], ';');

        // Data rows
        /** @var EServiceRequest $model */
        foreach ($dataProvider->getModels() as $model) {
            $user = $model->user;
            $userName = $user ? ($user->displayName ?? $user->username) : 'N/A';

            fputcsv($handle, [
                $model->id,
                $userName,
                $model->getTypeLabel(),
                $model->getSubTypeLabel() ?: '',
                $model->getStatusLabel(),
                $model->event_name ?: '',
                $model->date_start ?: '',
                $model->date_end ?: '',
                $model->observations ?: '',
                $model->admin_comment ?: '',
                $model->created_at,
                $model->updated_at,
            ], ';');
        }

        fclose($handle);

        return Yii::$app->response->sendFile($tempFile, $filename, [
            'mimeType' => 'text/csv',
            'inline' => false,
        ])->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tempFile) {
            // Clean up temporary file after sending
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        });
    }

    /**
     * Finds an EServiceRequest model by its primary key.
     *
     * @param int $id the request ID
     * @return EServiceRequest the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = EServiceRequest::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException(Yii::t('EserviceModule.base', 'Demande introuvable.'));
        }

        return $model;
    }
}
