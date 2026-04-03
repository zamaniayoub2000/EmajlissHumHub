<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */


use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/* @var $configuration \humhub\modules\collabora\models\forms\Configuration */
?>
<div class="panel panel-default">
    <div class="panel-heading"><strong><?= Yii::t('CollaboraModule.base', 'Collabora Online Integration') ?></strong></div>

    <div class="panel-body">

        <?php if (!Yii::$app->urlManager->enablePrettyUrl): ?>
            <p class="alert alert-danger">You need to enable "Pretty URLs" in order to use the Collabora
                integration.</p>
        <?php endif; ?>

        <?php $form = ActiveForm::begin() ?>

        <?= $form->field($configuration, 'wopiClientBaseUrl') ?>
        <?= $form->field($configuration, 'wopiHostBaseUrl') ?>

        <?= Button::save()->submit() ?>

        <?php ActiveForm::end() ?>
    </div>
</div>