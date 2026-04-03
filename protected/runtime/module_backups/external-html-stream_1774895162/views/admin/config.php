<?php

use humhub\modules\externalHtmlStream\models\ConfigForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @var \humhub\modules\ui\view\components\View $this
 * @var ConfigForm $model
 * @var \humhub\modules\space\models\Space[] $spaces
 */

$this->title = Yii::t('ExternalHtmlStreamModule.base', 'Configuration du module');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <i class="fa fa-cog"></i>
            Configuration — Majliss Sync & External HTML Stream
        </h4>
    </div>

    <div class="panel-body">
        <?php $form = ActiveForm::begin(['id' => 'config-form']); ?>

        <!-- ═══ Connexion Majliss ═══ -->
        <h5 style="border-bottom: 2px solid #667eea; padding-bottom: 8px; margin-top: 0; color: #667eea;">
            <i class="fa fa-database"></i> Connexion base WordPress Majliss
        </h5>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'majlissDbHost')->textInput(['placeholder' => 'localhost']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'majlissDbUser')->textInput(['placeholder' => 'user']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'majlissDbPass')->passwordInput(['placeholder' => '••••••••']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'majlissDbName')->textInput(['placeholder' => 'database_name']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'majlissDbPrefix')->textInput(['placeholder' => '4aNLlcLvO_']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'majlissBaseUrl')->textInput(['placeholder' => 'https://intranet.csefrs.ma']) ?>
            </div>
        </div>

        <!-- ═══ Synchronisation ═══ -->
        <h5 style="border-bottom: 2px solid #28a745; padding-bottom: 8px; margin-top: 25px; color: #28a745;">
            <i class="fa fa-exchange"></i> Paramètres de synchronisation
        </h5>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'targetSpaceId')->dropDownList(
                    ArrayHelper::map($spaces, 'id', 'name'),
                    ['prompt' => '— Sélectionner un espace —']
                ) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'batchLimit')->textInput(['type' => 'number', 'min' => 1, 'max' => 100]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'imageDownloadTimeout')->textInput(['type' => 'number', 'min' => 5, 'max' => 60]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'autoSyncEnabled')->checkbox() ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'fallbackImage')->textInput([
                    'placeholder' => 'https://example.com/default-image.jpg',
                ]) ?>
            </div>
        </div>

        <!-- ═══ Paramètres généraux ═══ -->
        <h5 style="border-bottom: 2px solid #17a2b8; padding-bottom: 8px; margin-top: 25px; color: #17a2b8;">
            <i class="fa fa-sliders"></i> Paramètres généraux
        </h5>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'enableCache')->checkbox() ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'allowIframes')->checkbox() ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'apiTimeout')->textInput(['type' => 'number', 'min' => 5, 'max' => 120]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'whitelistedDomains')->textInput([
                    'placeholder' => 'api.exemple.com, cdn.exemple.com',
                ]) ?>
            </div>
        </div>

        <hr>

        <div class="form-group">
            <?= Button::save()->submit() ?>
            <?= Button::defaultType('Retour au tableau de bord')->link(['index']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
