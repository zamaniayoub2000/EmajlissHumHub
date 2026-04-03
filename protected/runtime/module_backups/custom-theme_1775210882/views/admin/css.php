<?php
/**
 * Éditeur CSS personnalisé
 * @var \humhub\modules\customTheme\models\CustomThemeForm $model
 */

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\customTheme\assets\AdminAsset;

AdminAsset::register($this);
$this->title = Yii::t('CustomThemeModule.base', 'Custom Theme - CSS');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1><i class="fa fa-css3"></i> <?= Yii::t('CustomThemeModule.base', 'CSS personnalisé') ?></h1>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <?= $this->render('_nav', ['active' => 'css']) ?>
            </div>
            <div class="col-md-9">
                <?php $form = ActiveForm::begin(['id' => 'custom-theme-css-form']); ?>

                <div class="form-group">
                    <label class="ct-switch-inline">
                        <?= Html::activeCheckbox($model, 'css_active', ['label' => false]) ?>
                        <strong><?= Yii::t('CustomThemeModule.base', 'Activer le CSS personnalisé') ?></strong>
                    </label>
                </div>

                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    <?= Yii::t('CustomThemeModule.base', 'Le CSS ajouté ici sera prioritaire sur le thème Clean. Utilisez des sélecteurs spécifiques pour éviter les conflits.') ?>
                </div>

                <div class="form-group">
                    <?= Html::activeLabel($model, 'custom_css') ?>
                    <?= Html::activeTextarea($model, 'custom_css', [
                        'class' => 'form-control ct-code-editor',
                        'rows' => 30,
                        'id' => 'css-editor',
                        'data-mode' => 'css',
                        'style' => 'font-family: "Courier New", monospace; font-size: 13px; tab-size: 4;',
                    ]) ?>
                    <p class="help-block">
                        <?= Yii::t('CustomThemeModule.base', 'CSS pur sans balises &lt;style&gt;. Sera injecté via registerCss().') ?>
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
