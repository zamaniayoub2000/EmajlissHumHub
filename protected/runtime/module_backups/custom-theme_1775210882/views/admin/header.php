<?php
/**
 * Éditeur de header HTML
 * @var \humhub\modules\customTheme\models\CustomThemeForm $model
 */

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\customTheme\assets\AdminAsset;

AdminAsset::register($this);
$this->title = Yii::t('CustomThemeModule.base', 'Custom Theme - Header');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1><i class="fa fa-arrow-up"></i> <?= Yii::t('CustomThemeModule.base', 'Éditeur de Header') ?></h1>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <?= $this->render('_nav', ['active' => 'header']) ?>
            </div>
            <div class="col-md-9">
                <?php $form = ActiveForm::begin(['id' => 'custom-theme-header-form']); ?>

                <div class="form-group">
                    <label class="ct-switch-inline">
                        <?= Html::activeCheckbox($model, 'header_active', ['label' => false]) ?>
                        <strong><?= Yii::t('CustomThemeModule.base', 'Activer le header personnalisé') ?></strong>
                    </label>
                </div>

                <div class="form-group">
                    <?= Html::activeLabel($model, 'header_html') ?>
                    <?= Html::activeTextarea($model, 'header_html', [
                        'class' => 'form-control ct-code-editor',
                        'rows' => 25,
                        'id' => 'header-html-editor',
                        'data-mode' => 'htmlmixed',
                    ]) ?>
                    <p class="help-block">
                        <?= Yii::t('CustomThemeModule.base', 'Collez votre HTML complet du header (logo, navigation, boutons).') ?>
                    </p>
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-default" id="btn-preview-header">
                        <i class="fa fa-eye"></i> <?= Yii::t('CustomThemeModule.base', 'Aperçu') ?>
                    </button>
                </div>

                <div id="header-preview" class="ct-preview-zone" style="display:none;">
                    <h5><?= Yii::t('CustomThemeModule.base', 'Aperçu du header') ?></h5>
                    <div class="ct-preview-frame" id="header-preview-content"></div>
                </div>

                <hr>

                <div class="form-group">
                    <?= Html::submitButton(
                        '<i class="fa fa-save"></i> ' . Yii::t('CustomThemeModule.base', 'Enregistrer'),
                        ['class' => 'btn btn-primary']
                    ) ?>
                    <a href="<?= Url::to(['/custom-theme/admin/index']) ?>" class="btn btn-default">
                        <?= Yii::t('CustomThemeModule.base', 'Retour') ?>
                    </a>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var previewBtn = document.getElementById('btn-preview-header');
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            var content = document.getElementById('header-html-editor').value;
            var previewZone = document.getElementById('header-preview');
            var previewContent = document.getElementById('header-preview-content');
            previewZone.style.display = 'block';
            previewContent.innerHTML = content;
        });
    }
});
</script>
