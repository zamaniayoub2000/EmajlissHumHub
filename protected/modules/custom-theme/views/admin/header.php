<?php
/** @var \humhub\modules\customTheme\models\CustomThemeForm $model */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\customTheme\assets\AdminAsset;

AdminAsset::register($this);
$this->title = 'Custom Theme - Header';
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1><i class="fa fa-arrow-up"></i> Personnalisation du Header</h1>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <?= $this->render('_nav', ['active' => 'header']) ?>
            </div>
            <div class="col-md-9">

                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    <strong>Ce code modifie le header existant du thème Clean.</strong><br>
                    Utilisez <code>&lt;style&gt;</code> pour le CSS et <code>&lt;script&gt;</code> pour le JS.
                </div>

                <?php $form = ActiveForm::begin(['id' => 'ct-header-form']); ?>

                <div class="form-group">
                    <label>
                        <?= Html::activeCheckbox($model, 'header_active', ['label' => false]) ?>
                        <strong>Activer la personnalisation du header</strong>
                    </label>
                </div>

                <div class="form-group">
                    <label>Code de personnalisation (CSS + JS + HTML)</label>
                    <?= Html::activeTextarea($model, 'header_html', [
                        'class' => 'form-control ct-code-editor',
                        'rows' => 30,
                        'id' => 'header-editor',
                        'placeholder' => "<!-- Exemple -->\n<style>\n  .topbar, #topbar-first {\n    background: #6D1A36 !important;\n  }\n</style>\n\n<script>\n  // Ajouter un élément dans la navbar\n</script>",
                    ]) ?>
                    <p class="help-block">Exemples :</p>
                    <ul class="help-block" style="font-size: 12px; color: #888;">
                        <li>Changer les couleurs de la topbar avec <code>&lt;style&gt;</code></li>
                        <li>Remplacer le logo avec CSS</li>
                        <li>Ajouter des boutons/liens avec <code>&lt;script&gt;</code></li>
                        <li>Masquer des éléments du menu avec <code>display:none</code></li>
                    </ul>
                </div>

                <hr>

                <?= Html::submitButton('<i class="fa fa-save"></i> Enregistrer', ['class' => 'btn btn-primary']) ?>
                <a href="<?= Url::to(['/custom-theme/admin/index']) ?>" class="btn btn-default">Retour</a>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
