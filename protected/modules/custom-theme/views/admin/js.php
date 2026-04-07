<?php
/** @var \humhub\modules\customTheme\models\CustomThemeForm $model */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\customTheme\assets\AdminAsset;

AdminAsset::register($this);
$this->title = 'Custom Theme - JavaScript';
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1><i class="fa fa-code"></i> JavaScript personnalisé</h1>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <?= $this->render('_nav', ['active' => 'js']) ?>
            </div>
            <div class="col-md-9">
                <?php $form = ActiveForm::begin(['id' => 'ct-js-form']); ?>

                <div class="form-group">
                    <label>
                        <?= Html::activeCheckbox($model, 'js_active', ['label' => false]) ?>
                        <strong>Activer le JavaScript personnalisé</strong>
                    </label>
                </div>

                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i>
                    Du JavaScript mal écrit peut casser la plateforme. Testez en local avant.
                </div>

                <div class="form-group">
                    <label>JavaScript</label>
                    <?= Html::activeTextarea($model, 'custom_js', [
                        'class' => 'form-control ct-code-editor',
                        'rows' => 30,
                        'id' => 'js-editor',
                    ]) ?>
                    <p class="help-block">JavaScript pur sans balises <code>&lt;script&gt;</code>.</p>
                </div>

                <hr>

                <?= Html::submitButton('<i class="fa fa-save"></i> Enregistrer', ['class' => 'btn btn-primary']) ?>
                <a href="<?= Url::to(['/custom-theme/admin/index']) ?>" class="btn btn-default">Retour</a>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
