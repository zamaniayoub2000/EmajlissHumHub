<?php

use humhub\modules\externalHtmlStream\models\ExternalPost;
use humhub\modules\space\models\Space;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var \humhub\modules\ui\view\components\View $this
 * @var ExternalPost $model
 * @var Space[] $spaces
 */
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <i class="fa fa-globe"></i>
            <?= $model->isNewRecord
                ? Yii::t('ExternalHtmlStreamModule.base', 'Nouvelle publication HTML externe')
                : Yii::t('ExternalHtmlStreamModule.base', 'Modifier : {title}', ['title' => $model->title])
            ?>
        </h4>
    </div>

    <div class="panel-body">
        <?php $form = ActiveForm::begin(['id' => 'external-post-form']); ?>

        <div class="row">
            <div class="col-md-8">
                <?= $form->field($model, 'title')->textInput([
                    'maxlength' => 255,
                    'placeholder' => 'Titre de la publication',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'space_id')->dropDownList(
                    ArrayHelper::map($spaces, 'id', 'name'),
                    ['prompt' => '— Sélectionner un espace —']
                ) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <?= $form->field($model, 'api_url')->textInput([
                    'placeholder' => 'https://api.example.com/content',
                    'type' => 'url',
                ]) ?>
                <p class="help-block">
                    <i class="fa fa-info-circle"></i>
                    L'API doit retourner du HTML ou du JSON avec un champ "html".
                </p>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'refresh_interval')->dropDownList([
                    60    => '1 minute',
                    300   => '5 minutes',
                    600   => '10 minutes',
                    1800  => '30 minutes',
                    3600  => '1 heure',
                    7200  => '2 heures',
                    21600 => '6 heures',
                    43200 => '12 heures',
                    86400 => '24 heures',
                ]) ?>
            </div>
        </div>

        <?php if (!$model->isNewRecord && !empty($model->cached_html)): ?>
            <div class="form-group">
                <label>Aperçu du contenu actuel</label>
                <div class="well" style="max-height: 300px; overflow-y: auto; background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px;">
                    <?= $model->cached_html ?>
                </div>
                <?php if ($model->last_fetched_at): ?>
                    <small class="text-muted">
                        <i class="fa fa-clock-o"></i>
                        Dernière récupération : <?= Yii::$app->formatter->asDatetime($model->last_fetched_at) ?>
                    </small>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <hr>

        <div class="form-group">
            <?= Button::save()->submit() ?>
            <?= Button::defaultType('Annuler')->link(['index']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
