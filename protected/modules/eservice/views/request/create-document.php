<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use humhub\modules\eservice\models\EServiceRequest;

/** @var yii\web\View $this */
/** @var EServiceRequest $model */
/** @var string $type */
/** @var string $subType */

\humhub\modules\eservice\assets\EServiceAsset::register($this);

$subTypeLabels = [
    'reservation' => 'R&eacute;servation d\'un ouvrage pour pr&ecirc;t physique',
    'bulletin' => 'Demande d\'un Bulletin Officiel',
    'dossier' => 'Demande de constitution d\'un dossier documentaire',
    'documentation' => 'Demande de documentation diverse',
    'proposition' => 'Proposition de titres d\'ouvrages pour acquisition',
];

$subTypeLabel = isset($subTypeLabels[$subType]) ? $subTypeLabels[$subType] : 'Demande de Document';
$this->title = strip_tags($subTypeLabel);

// Extra data from model (for re-populating on validation error)
$extraData = is_array($model->extra_data) ? $model->extra_data : [];
?>

<div class="container">
    <!-- Navigation -->
    <div class="es-nav">
        <a href="<?= Url::to(['/eservice/request/documents']) ?>" class="es-back-btn">
            <i class="fa fa-arrow-left"></i> Retour aux documents
        </a>
        <a href="<?= Url::to(['/eservice/index/dashboard']) ?>" class="es-back-btn">
            <i class="fa fa-list-alt"></i> Mes demandes
        </a>
    </div>

    <!-- Form Card -->
    <div class="es-form">
        <div class="es-form-header">
            <h2><i class="fa fa-file-alt"></i> <?= $subTypeLabel ?></h2>
            <p>Remplissez le formulaire ci-dessous pour soumettre votre demande documentaire</p>
        </div>

        <div class="es-form-body">
            <?php $form = ActiveForm::begin([
                'id' => 'eservice-document-form',
                'options' => ['enctype' => 'multipart/form-data'],
            ]); ?>

            <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'sub_type')->hiddenInput()->label(false) ?>

            <?php if ($subType === 'reservation'): ?>
                <!-- Reservation: titre de l'ouvrage, auteur -->
                <div class="form-group">
                    <label class="control-label">Titre de l'ouvrage</label>
                    <?= Html::textInput('EServiceRequest[extra_data][titre_ouvrage]',
                        isset($extraData['titre_ouvrage']) ? $extraData['titre_ouvrage'] : '',
                        ['class' => 'form-control', 'placeholder' => 'Saisissez le titre de l\'ouvrage']
                    ) ?>
                </div>
                <div class="form-group">
                    <label class="control-label">Auteur</label>
                    <?= Html::textInput('EServiceRequest[extra_data][auteur]',
                        isset($extraData['auteur']) ? $extraData['auteur'] : '',
                        ['class' => 'form-control', 'placeholder' => 'Saisissez le nom de l\'auteur']
                    ) ?>
                </div>

            <?php elseif ($subType === 'bulletin'): ?>
                <!-- Bulletin: numero, date de publication -->
                <div class="form-group">
                    <label class="control-label">Num&eacute;ro du Bulletin</label>
                    <?= Html::textInput('EServiceRequest[extra_data][numero_bulletin]',
                        isset($extraData['numero_bulletin']) ? $extraData['numero_bulletin'] : '',
                        ['class' => 'form-control', 'placeholder' => 'Saisissez le num&eacute;ro du bulletin']
                    ) ?>
                </div>
                <div class="form-group">
                    <label class="control-label">Date de publication</label>
                    <?= Html::input('date', 'EServiceRequest[extra_data][date_publication]',
                        isset($extraData['date_publication']) ? $extraData['date_publication'] : '',
                        ['class' => 'form-control']
                    ) ?>
                </div>

            <?php elseif ($subType === 'dossier'): ?>
                <!-- Dossier: theme du dossier -->
                <div class="form-group">
                    <label class="control-label">Th&egrave;me du dossier</label>
                    <?= Html::textInput('EServiceRequest[extra_data][theme_dossier]',
                        isset($extraData['theme_dossier']) ? $extraData['theme_dossier'] : '',
                        ['class' => 'form-control', 'placeholder' => 'Saisissez le th&egrave;me du dossier documentaire']
                    ) ?>
                </div>

            <?php elseif ($subType === 'documentation'): ?>
                <!-- Documentation: description -->
                <div class="form-group">
                    <label class="control-label">Description</label>
                    <?= Html::textarea('EServiceRequest[extra_data][description]',
                        isset($extraData['description']) ? $extraData['description'] : '',
                        ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'D&eacute;crivez la documentation recherch&eacute;e']
                    ) ?>
                </div>

            <?php elseif ($subType === 'proposition'): ?>
                <!-- Proposition: titre, auteur, editeur -->
                <div class="form-group">
                    <label class="control-label">Titre propos&eacute;</label>
                    <?= Html::textInput('EServiceRequest[extra_data][titre_propose]',
                        isset($extraData['titre_propose']) ? $extraData['titre_propose'] : '',
                        ['class' => 'form-control', 'placeholder' => 'Saisissez le titre propos&eacute;']
                    ) ?>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Auteur</label>
                            <?= Html::textInput('EServiceRequest[extra_data][auteur]',
                                isset($extraData['auteur']) ? $extraData['auteur'] : '',
                                ['class' => 'form-control', 'placeholder' => 'Nom de l\'auteur']
                            ) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">&Eacute;diteur</label>
                            <?= Html::textInput('EServiceRequest[extra_data][editeur]',
                                isset($extraData['editeur']) ? $extraData['editeur'] : '',
                                ['class' => 'form-control', 'placeholder' => 'Nom de l\'&eacute;diteur']
                            ) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Observations (common) -->
            <?= $form->field($model, 'observations')->textarea([
                'rows' => 4,
                'class' => 'form-control',
                'placeholder' => 'Ajoutez des observations ou pr&eacute;cisions compl&eacute;mentaires...',
            ])->label('Observations') ?>

            <!-- File Upload -->
            <div class="form-group">
                <label class="control-label">Pi&egrave;ces jointes</label>
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

            <?php ActiveForm::end(); ?>
        </div>

        <div class="es-form-footer">
            <button type="submit" form="eservice-document-form" class="es-btn-primary">
                <i class="fa fa-paper-plane"></i> Soumettre la demande
            </button>
            <a href="<?= Url::to(['/eservice/request/documents']) ?>" class="es-btn-secondary">
                <i class="fa fa-times"></i> Annuler
            </a>
        </div>
    </div>
</div>
