<?php
/**
 * Éditeur JavaScript personnalisé
 * @var \humhub\modules\customTheme\models\CustomThemeForm $model
 */

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\customTheme\assets\AdminAsset;

AdminAsset::register($this);
$this->title = Yii::t('CustomThemeModule.base', 'Custom Theme - JavaScript');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1><i class="fa fa-code"></i> <?= Yii::t('CustomThemeModule.base', 'JavaScript personnalisé') ?></h1>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <?= $this->render('_nav', ['active' => 'js']) ?>
            </div>
            <div class="col-md-9">
                <?php $form = ActiveForm::begin(['id' => 'custom-theme-js-form']); ?>

                <div class="form-group">
                    <label class="ct-switch-inline">
                        <?= Html::activeCheckbox($model, 'js_active', ['label' => false]) ?>
                        <strong><?= Yii::t('CustomThemeModule.base', 'Activer le JavaScript personnalisé') ?></strong>
                    </label>
                </div>

                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i>
                    <?= Yii::t('CustomThemeModule.base', 'Attention : du JavaScript mal écrit peut casser la plateforme. Testez toujours en local avant.') ?>
                </div>

                <div class="form-group">
                    <?= Html::activeLabel($model, 'custom_js') ?>
                    <?= Html::activeTextarea($model, 'custom_js', [
                        'class' => 'form-control ct-code-editor',
                        'rows' => 30,
                        'id' => 'js-editor',
                        'data-mode' => 'javascript',
                        'style' => 'font-family: "Courier New", monospace; font-size: 13px; tab-size: 4;',
                    ]) ?>
                    <p class="help-block">
                        <?= Yii::t('CustomThemeModule.base', 'JavaScript pur sans balises &lt;script&gt;. Sera injecté en fin de page.') ?>
                    </p>
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
