<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\collabora\controllers;

use humhub\components\Controller;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\collabora\models\CreateFile;
use humhub\modules\collabora\Module;
use Yii;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;

class CreateController extends Controller
{
    /**
     * @inheritdoc
     * @var Module
     */
    public $module;

    public function actionIndex()
    {
        $model = new CreateFile();

        if ($model->load(Yii::$app->request->post())) {
            if ($file = $model->save()) {
                return $this->asJson([
                    'success' => true,
                    'file' => FileHelper::getFileInfos($file),
                    'editFormUrl' => Url::to(['/collabora/editor', 'guid' => $file->guid]),
                ]);
            } else {
                return $this->asJson([
                    'success' => false,
                    'output' => $this->renderAjax('index', ['model' => $model]),
                ]);
            }
        }

        return $this->renderAjax('index', ['model' => $model]);
    }

}
