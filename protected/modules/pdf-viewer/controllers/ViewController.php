<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\pdfViewer\controllers;

use humhub\components\Controller;
use humhub\modules\file\models\File;
use humhub\modules\pdfViewer\assets\PdfJsAssets;
use humhub\modules\pdfViewer\assets\PdfViewerAssets;
use humhub\modules\pdfViewer\helpers\PdfHelper;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class ViewController extends Controller
{
    public function actionIndex()
    {
        return $this->renderAjax('index', ['file' => $this->getFile()]);
    }

    public function actionOpen()
    {
        if ($this->getFile()->getUrl() !== Yii::$app->request->get('file')) {
            throw new BadRequestHttpException('Denied request with a wrong file guid!');
        }

        return $this->renderPdfViewer();
    }

    private function getFile(): ?File
    {
        $guid = Yii::$app->request->get('guid', Yii::$app->request->post('guid'));

        if (empty($guid)) {
            throw new BadRequestHttpException('Wrong request without file guid!');
        }

        $file = File::findOne(['guid' => $guid]);

        if (!PdfHelper::isPdfFile($file)) {
            throw new BadRequestHttpException(Yii::t('PdfViewerModule.base', 'Could not view the requested file!'));
        }

        return $file;
    }

    /**
     * Render PDF viewer from source HTML
     *
     * @return string
     * @throws ServerErrorHttpException
     */
    private function renderPdfViewer(): string
    {
        $sourcePath = Yii::getAlias('@pdf-viewer/vendor/clean-composer-packages/pdf-js/web/viewer.html');

        if (!is_readable($sourcePath)) {
            throw new ServerErrorHttpException('File "' . $sourcePath . '" is not readable!');
        }

        $sourceCode = file_get_contents($sourcePath);
        $assetsUrl = PdfJsAssets::register($this->view)->baseUrl . '/web/';
        $sourceCode = preg_replace('/(<script.+?src="|<link.+?href=")/i', '$1' . $assetsUrl, $sourceCode);

        $styles = '<link href="' . PdfViewerAssets::register($this->view)->baseUrl . '/css/pdf-viewer-iframe.css" rel="stylesheet">';
        return str_replace('</head>', $styles . '</head>', $sourceCode);
    }
}
