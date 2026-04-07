<?php
/** @var \humhub\modules\customTheme\models\CustomThemeForm $model */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\customTheme\assets\AdminAsset;

AdminAsset::register($this);
$this->title = 'Custom Theme - Footer';
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1><i class="fa fa-arrow-down"></i> Éditeur de Footer</h1>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <?= $this->render('_nav', ['active' => 'footer']) ?>
            </div>
            <div class="col-md-9">
                <?php $form = ActiveForm::begin(['id' => 'ct-footer-form']); ?>

                <div class="form-group">
                    <label>
                        <?= Html::activeCheckbox($model, 'footer_active', ['label' => false]) ?>
                        <strong>Activer le footer personnalisé</strong>
                    </label>
                </div>

                <div class="form-group">
                    <label>Footer HTML</label>
                    <?= Html::activeTextarea($model, 'footer_html', [
                        'class' => 'form-control ct-code-editor',
                        'rows' => 25,
                        'id' => 'footer-editor',
                    ]) ?>
                    <p class="help-block">
                        HTML complet du footer avec CSS et JS intégrés si nécessaire.
                    </p>
                </div>

                <button type="button" class="btn btn-default" id="btn-preview">
                    <i class="fa fa-eye"></i> Aperçu
                </button>

                <div id="footer-preview" class="ct-preview-zone" style="display:none;">
                    <h5>Aperçu</h5>
                    <div class="ct-preview-frame" id="preview-content"></div>
                </div>

                <hr>

                <?= Html::submitButton('<i class="fa fa-save"></i> Enregistrer', ['class' => 'btn btn-primary']) ?>
                <a href="<?= Url::to(['/custom-theme/admin/index']) ?>" class="btn btn-default">Retour</a>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btn-preview').addEventListener('click', function() {
        document.getElementById('footer-preview').style.display = 'block';
        document.getElementById('preview-content').innerHTML = document.getElementById('footer-editor').value;
    });
});
</script>
