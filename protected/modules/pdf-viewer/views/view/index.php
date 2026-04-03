<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\file\models\File;
use humhub\modules\pdfViewer\assets\PdfViewerAssets;
use humhub\modules\pdfViewer\helpers\Url;
use humhub\widgets\modal\Modal;
use yii\web\View;

/* @var File $file */
/* @var View $this */

PdfViewerAssets::register($this);
?>
<?php Modal::beginDialog([
    'header' => $file->file_name,
    'size' => Modal::SIZE_FULL_SCREEN,
]) ?>
    <iframe id="pdf-js-viewer" src="<?= Url::toViewer($file) ?>" title="webviewer" frameborder="0"></iframe>
<?php Modal::endDialog() ?>
