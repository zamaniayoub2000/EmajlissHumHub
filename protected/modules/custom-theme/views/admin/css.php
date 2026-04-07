<?php
/** @var \humhub\modules\customTheme\models\CustomThemeForm $model */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\customTheme\assets\AdminAsset;

AdminAsset::register($this);
$this->title = 'Custom Theme - CSS';
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1><i class="fa fa-css3"></i> CSS personnalisé</h1>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <?= $this->render('_nav', ['active' => 'css']) ?>
            </div>
            <div class="col-md-9">
                <?php $form = ActiveForm::begin(['id' => 'ct-css-form']); ?>

                <div class="form-group">
                    <label>
                        <?= Html::activeCheckbox($model, 'css_active', ['label' => false]) ?>
                        <strong>Activer le CSS personnalisé</strong>
                    </label>
                </div>

                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    Le CSS sera prioritaire sur le thème Clean. Utilisez des sélecteurs spécifiques.
                </div>

                <div class="form-group">
                    <label>CSS</label>
                    <?= Html::activeTextarea($model, 'custom_css', [
                        'class' => 'form-control ct-code-editor',
                        'rows' => 30,
                        'id' => 'css-editor',
                    ]) ?>
                    <p class="help-block">CSS pur sans balises <code>&lt;style&gt;</code>.</p>
                </div>

                <hr>

                <?= Html::submitButton('<i class="fa fa-save"></i> Enregistrer', ['class' => 'btn btn-primary']) ?>
                <a href="<?= Url::to(['/custom-theme/admin/index']) ?>" class="btn btn-default">Retour</a>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
