<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\eservice\models\EServiceRequest;

/** @var yii\web\View $this */
/** @var EServiceRequest $model */
/** @var string $type */

\humhub\modules\eservice\assets\EServiceAsset::register($this);

$typeLabels = [
    'hebergement' => 'Demande d\'Hébergement',
    'billet_avion' => 'Demande de Billet d\'Avion',
    'indemnite' => 'Dépôt de documents et justificatifs',
    'support' => 'Demande de Support',
];

$typeLabel = isset($typeLabels[$type]) ? $typeLabels[$type] : 'Nouvelle Demande';
$this->title = strip_tags($typeLabel);

$events = EServiceRequest::getEventsList();
?>

<div class="container">
    <!-- Navigation -->
    <div class="es-nav">
        <a href="<?= Url::to(['/eservice/index/index']) ?>" class="es-back-btn">
            <i class="fa fa-arrow-left"></i> Retour aux services
        </a>
        <a href="<?= Url::to(['/eservice/index/dashboard']) ?>" class="es-back-btn">
            <i class="fa fa-list-alt"></i> Mes demandes
        </a>
    </div>

    <!-- Form Card -->
    <div class="es-form">
        <div class="es-form-header">
            <h2><?= $typeLabel ?></h2>
            <p>Remplissez le formulaire ci-dessous pour soumettre votre demande</p>
        </div>

        <div class="es-form-body">
            <?php $form = ActiveForm::begin([
                'id' => 'eservice-request-form',
                'options' => ['enctype' => 'multipart/form-data'],
            ]); ?>

            <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>

            <?php if ($type === 'billet_avion' || $type === 'hebergement' || $type === 'indemnite'): ?>
                <!-- Event Selection -->
                <?= $form->field($model, 'event_name')->dropDownList(
                    $events,
                    ['prompt' => '-- Sélectionnez un type de réunion --', 'class' => 'form-control', 'id' => 'event-name-select']
                )->label('Type de réunion') ?>

                <!-- Champ Préciser (affiché quand "Autre" est sélectionné) -->
                <div class="form-group" id="preciser-container" style="display:none;">
                    <label class="control-label">Préciser</label>
                    <input type="text" name="EServiceRequest[extra_data]" class="form-control" placeholder="Veuillez préciser le type de réunion..." />
                </div>
            <?php endif; ?>

            <?php if ($type === 'billet_avion'): ?>
                <!-- Billet d'avion specific fields -->
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'date_start')->input('date', ['class' => 'form-control'])->label('Date de départ') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'date_end')->input('date', ['class' => 'form-control'])->label('Date de retour') ?>
                    </div>
                </div>

                <?= $form->field($model, 'flight_plan')->textarea([
                    'rows' => 4,
                    'class' => 'form-control',
                    'placeholder' => 'Décrivez votre plan de vol (villes, escales, etc.)',
                ])->label('Plan de vol') ?>

                <!-- Shuttle toggles -->
                <div class="form-group">
                    <div class="es-toggle">
                        <label class="es-toggle-switch">
                            <?= Html::activeHiddenInput($model, 'shuttle_arrival', ['value' => '0']) ?>
                            <?= Html::activeCheckbox($model, 'shuttle_arrival', [
                                'label' => false,
                                'value' => '1',
                                'uncheck' => null,
                            ]) ?>
                            <span class="es-toggle-slider"></span>
                        </label>
                        <span class="es-toggle-label">Navette &agrave; l'arrivée</span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="es-toggle">
                        <label class="es-toggle-switch">
                            <?= Html::activeHiddenInput($model, 'shuttle_departure', ['value' => '0']) ?>
                            <?= Html::activeCheckbox($model, 'shuttle_departure', [
                                'label' => false,
                                'value' => '1',
                                'uncheck' => null,
                            ]) ?>
                            <span class="es-toggle-slider"></span>
                        </label>
                        <span class="es-toggle-label">Navette au départ</span>
                    </div>
                </div>

            <?php elseif ($type === 'hebergement'): ?>
                <!-- Hebergement specific fields -->
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'date_start')->input('date', ['class' => 'form-control'])->label('Date d\'arrivée') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'date_end')->input('date', ['class' => 'form-control'])->label('Date de départ') ?>
                    </div>
                </div>

            <?php elseif ($type === 'indemnite'): ?>
                <!-- Indemnite specific fields -->

            <?php elseif ($type === 'support'): ?>
                <!-- Support - no event needed, just description -->
            <?php endif; ?>

            <!-- Observations (common to all types) -->
            <?= $form->field($model, 'observations')->textarea([
                'rows' => 4,
                'class' => 'form-control',
                'placeholder' => $type === 'support'
                    ? 'Décrivez votre problème ou votre demande en détail...'
                    : 'Ajoutez des observations ou précisions complémentaires...',
            ])->label($type === 'support' ? 'Description de la demande' : 'Observations') ?>

            <!-- File Upload (for support and indemnite, also available for others) -->
            <?php if ($type === 'support' || $type === 'indemnite'): ?>
                <div class="form-group">
                    <label class="control-label">Pièces jointes</label>
                    <div class="es-file-upload">
                        <div class="es-file-upload-icon">
                            <i class="fa fa-cloud-upload-alt"></i>
                        </div>
                        <div class="es-file-upload-text">
                            <strong>Cliquez ou glissez</strong> vos fichiers ici<br>
                            <small>PDF, JPG, PNG - Max 10 Mo par fichier</small>
                        </div>
                        <input type="file" name="attachments[]" multiple
                               accept=".pdf,.jpg,.jpeg,.png">
                        <div class="es-file-preview"></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php ActiveForm::end(); ?>
        </div>

        <div class="es-form-footer">
            <button type="submit" form="eservice-request-form" class="es-btn-primary">
                <i class="fa fa-paper-plane"></i> Soumettre la demande
            </button>
            <a href="<?= Url::to(['/eservice/index/index']) ?>" class="es-btn-secondary">
                <i class="fa fa-times"></i> Annuler
            </a>
        </div>
    </div>
</div>
