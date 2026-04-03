<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\collabora\controllers;

use humhub\components\Controller;
use humhub\modules\collabora\Module;
use humhub\modules\collabora\services\CollaboraService;
use humhub\modules\file\models\File;
use Yii;
use yii\web\HttpException;

/**
 * @property Module $module
 */
class EditorController extends Controller
{
    /**
     * Edit the text file in modal
     *
     * @return string
     * @throws HttpException
     */
    public function actionIndex()
    {
        if (!$this->module->getConfiguration()->isConfigured()) {
            throw new HttpException(500, 'Collabora Online is not configured!');
        }

        $file = $this->getFile();
        if (!$file->canView()) {
            throw new HttpException(401);
        }

        $collabora = new CollaboraService();
        return $this->renderAjax('index', [
            'file' => $file,
            'wopiUrl' => $collabora->buildUrl($file, Yii::$app->user->getIdentity()),
        ]);
    }

    protected function getFile(): File
    {
        $guid = Yii::$app->request->get('guid', Yii::$app->request->post('guid'));
        $file = File::findOne(['guid' => $guid]);
        if (empty($file)) {
            throw new HttpException(404, Yii::t('CollaboraModule.base', 'Could not find requested file!'));
        }

        return $file;
    }
}
