<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\eservice\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use humhub\components\Controller;
use humhub\modules\eservice\models\EServiceRequest;
use humhub\modules\eservice\models\EServiceFile;
use humhub\modules\eservice\models\EServiceStatusLog;

/**
 * RequestController handles CRUD operations for E-Service requests.
 *
 * Provides actions for creating, viewing, and downloading service requests
 * and their attached files.
 *
 * @package humhub\modules\eservice\controllers
 */
class RequestController extends Controller
{
    /**
     * Allowed MIME types for file uploads.
     */
    const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/jpg',
    ];

    /**
     * Allowed file extensions for uploads.
     */
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

    /**
     * {@inheritdoc}
     *
     * Requires authentication for all actions.
     *
     * @return array the behavior configuration
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
     * Displays the document sub-type selection page.
     *
     * Renders the document cards partial allowing users to choose
     * which type of document request to create.
     *
     * @return string the rendered view
     */
    public function actionDocuments()
    {
        return $this->render('_document_cards');
    }

    /**
     * Creates a new E-Service request of the given type.
     *
     * On GET, displays the creation form. On POST, validates and saves the
     * request, processes file uploads, creates an initial status log entry,
     * and redirects to the dashboard with a success flash message.
     *
     * @param string $type the service request type (must be a valid EServiceRequest type)
     * @return string|\yii\web\Response the rendered form or a redirect response
     * @throws NotFoundHttpException if the type is not valid
     */
    public function actionCreate($type)
    {
        if (!array_key_exists($type, EServiceRequest::getTypesList())) {
            throw new NotFoundHttpException(Yii::t('EserviceModule.base', 'Type de demande invalide.'));
        }

        $model = new EServiceRequest();
        $model->type = $type;
        $model->user_id = Yii::$app->user->id;
        $model->status = EServiceRequest::STATUS_PENDING;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save(false)) {
                // Process file uploads
                $this->processFileUploads($model);

                // Create initial status log entry
                $this->createStatusLog($model, null, EServiceRequest::STATUS_PENDING, Yii::t('EserviceModule.base', 'Demande creee.'));

                Yii::$app->session->setFlash('success', Yii::t('EserviceModule.base', 'Votre demande a ete soumise avec succes.'));

                return $this->redirect(['/eservice/index/dashboard']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'type' => $type,
        ]);
    }

    /**
     * Creates a new document-type E-Service request with a specific sub-type.
     *
     * Similar to actionCreate but specifically for document requests. Sets the
     * type to 'document' and applies the given sub-type.
     *
     * @param string $subType the document sub-type (must be a valid EServiceRequest sub-type)
     * @return string|\yii\web\Response the rendered form or a redirect response
     * @throws NotFoundHttpException if the sub-type is not valid
     */
    public function actionCreateDocument($subType)
    {
        if (!array_key_exists($subType, EServiceRequest::getSubTypesList())) {
            throw new NotFoundHttpException(Yii::t('EserviceModule.base', 'Sous-type de document invalide.'));
        }

        $model = new EServiceRequest();
        $model->type = EServiceRequest::TYPE_DOCUMENT;
        $model->sub_type = $subType;
        $model->user_id = Yii::$app->user->id;
        $model->status = EServiceRequest::STATUS_PENDING;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save(false)) {
                // Process file uploads
                $this->processFileUploads($model);

                // Create initial status log entry
                $this->createStatusLog($model, null, EServiceRequest::STATUS_PENDING, Yii::t('EserviceModule.base', 'Demande de document creee.'));

                Yii::$app->session->setFlash('success', Yii::t('EserviceModule.base', 'Votre demande de document a ete soumise avec succes.'));

                return $this->redirect(['/eservice/index/dashboard']);
            }
        }

        return $this->render('create-document', [
            'model' => $model,
            'type' => EServiceRequest::TYPE_DOCUMENT,
            'subType' => $subType,
        ]);
    }

    /**
     * Displays the details of a specific E-Service request.
     *
     * Only the request owner or an admin may view the request.
     * Loads related files and status logs via eager loading.
     *
     * @param int $id the request ID
     * @return string the rendered view
     * @throws NotFoundHttpException if the request is not found
     * @throws ForbiddenHttpException if the current user is not authorized
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess($model);

        // Eager load related data
        $model->populateRelation('files', $model->getFiles()->all());
        $model->populateRelation('statusLogs', $model->getStatusLogs()->all());

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Downloads an attached file.
     *
     * Verifies that the current user is the request owner or an admin
     * before sending the file.
     *
     * @param int $id the EServiceFile ID
     * @return \yii\web\Response the file download response
     * @throws NotFoundHttpException if the file or its physical path is not found
     * @throws ForbiddenHttpException if the current user is not authorized
     */
    public function actionDownload($id)
    {
        $file = EServiceFile::findOne($id);
        if ($file === null) {
            throw new NotFoundHttpException(Yii::t('EserviceModule.base', 'Fichier introuvable.'));
        }

        // Verify access through the parent request
        $request = $file->request;
        if ($request === null) {
            throw new NotFoundHttpException(Yii::t('EserviceModule.base', 'Demande associee introuvable.'));
        }

        $this->checkAccess($request);

        $filePath = Yii::getAlias('@webroot') . '/' . $file->file_path;
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException(Yii::t('EserviceModule.base', 'Le fichier physique est introuvable.'));
        }

        return Yii::$app->response->sendFile($filePath, $file->original_name, [
            'mimeType' => $file->mime_type,
            'inline' => false,
        ]);
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

    /**
     * Checks that the current user is authorized to access the given request.
     *
     * Access is granted if the user is the request owner or a platform admin.
     *
     * @param EServiceRequest $model the request to check access for
     * @throws ForbiddenHttpException if access is denied
     */
    protected function checkAccess(EServiceRequest $model)
    {
        $currentUserId = Yii::$app->user->id;
        $isAdmin = Yii::$app->user->isAdmin();

        if ($model->user_id != $currentUserId && !$isAdmin) {
            throw new ForbiddenHttpException(Yii::t('EserviceModule.base', 'Vous n\'etes pas autorise a acceder a cette demande.'));
        }
    }

    /**
     * Processes uploaded files for a given request.
     *
     * Validates MIME types and extensions, generates unique filenames,
     * creates the upload directory if needed, moves the files, and
     * creates EServiceFile records in the database.
     *
     * @param EServiceRequest $model the parent request model
     * @return void
     */
    protected function processFileUploads(EServiceRequest $model)
    {
        $uploadedFiles = UploadedFile::getInstancesByName('attachments');

        if (empty($uploadedFiles)) {
            return;
        }

        $uploadDir = Yii::getAlias('@webroot') . '/uploads/eservice/' . $model->id;
        FileHelper::createDirectory($uploadDir, 0775, true);

        foreach ($uploadedFiles as $uploadedFile) {
            // Validate file extension
            $extension = strtolower($uploadedFile->extension);
            if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                Yii::warning("Rejected file upload with extension: {$extension}", 'eservice');
                continue;
            }

            // Validate MIME type
            if (!in_array($uploadedFile->type, self::ALLOWED_MIME_TYPES)) {
                Yii::warning("Rejected file upload with MIME type: {$uploadedFile->type}", 'eservice');
                continue;
            }

            // Generate unique filename
            $uniqueName = uniqid('eservice_', true) . '.' . $extension;
            $filePath = $uploadDir . '/' . $uniqueName;

            if ($uploadedFile->saveAs($filePath)) {
                $fileModel = new EServiceFile();
                $fileModel->request_id = $model->id;
                $fileModel->filename = $uniqueName;
                $fileModel->original_name = $uploadedFile->name;
                $fileModel->mime_type = $uploadedFile->type;
                $fileModel->file_size = $uploadedFile->size;
                $fileModel->file_path = 'uploads/eservice/' . $model->id . '/' . $uniqueName;

                if (!$fileModel->save()) {
                    Yii::error('Failed to save EServiceFile: ' . json_encode($fileModel->errors), 'eservice');
                }
            } else {
                Yii::error("Failed to save uploaded file: {$uploadedFile->name}", 'eservice');
            }
        }
    }

    /**
     * Creates a status log entry for a request.
     *
     * @param EServiceRequest $model the request model
     * @param string|null $oldStatus the previous status (null for initial creation)
     * @param string $newStatus the new status
     * @param string|null $comment optional comment for the log entry
     * @return bool whether the log was saved successfully
     */
    protected function createStatusLog(EServiceRequest $model, $oldStatus, $newStatus, $comment = null)
    {
        $log = new EServiceStatusLog();
        $log->request_id = $model->id;
        $log->old_status = $oldStatus;
        $log->new_status = $newStatus;
        $log->comment = $comment;
        $log->changed_by = Yii::$app->user->id;

        if (!$log->save()) {
            Yii::error('Failed to save EServiceStatusLog: ' . json_encode($log->errors), 'eservice');
            return false;
        }

        return true;
    }
}
