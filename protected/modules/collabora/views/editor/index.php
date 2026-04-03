<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\file\models\File;
use humhub\modules\collabora\assets\Assets;
use humhub\widgets\modal\Modal;

/* @var $this View */
/* @var $file File */
/* @var $wopiUrl string */

Assets::register($this);
?>
<?php $form = Modal::beginFormDialog([
        'id' => 'collabora-modal',
        'header' => Yii::t(
                'CollaboraModule.base',
                '<strong>Edit file:</strong>  {fileName}',
                ['fileName' => Html::encode($file->file_name)]
        ),
        'size' => Modal::SIZE_FULL_SCREEN,
        'form' => ['acknowledge' => true],
]) ?>

<div data-ui-widget="collabora.Editor" data-ui-init data-wopi-url="<?= Html::encode($wopiUrl) ?>">
    <iframe id="collabora-online-viewer"
            name="collabora-online-viewer"
            allow="clipboard-read *; clipboard-write *">
    </iframe>

    <!--
    // Example shows we should POST into Iframe with access token
    <form enctype="multipart/form-data" method="post" target="collabora-online-viewer" id="collabora-submit-form">
        <input name="access_token" value="test" type="hidden"/>
        <input type="submit" value=""/>
    </form>
    -->
</div>

<?php Modal::endFormDialog() ?>
