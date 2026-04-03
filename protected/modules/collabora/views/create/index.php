<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\modules\collabora\assets\Assets;
use humhub\modules\collabora\models\CreateFile;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var View $this */
/* @var CreateFile $model */

Assets::register($this);
?>
<?php $form = Modal::beginFormDialog([
    'header' => Yii::t('CollaboraModule.base', '<strong>Create</strong> file'),
    'footer' => ModalButton::cancel()
        . ModalButton::save()->submit()->action('collabora.createSubmit'),
]) ?>
<?= $form->field($model, 'fileName')->textInput(['autofocus' => '']) ?>
<?= $form->field($model, 'fileType')->dropDownList($model->getFileTypes()) ?>
<?php Modal::endFormDialog() ?>
