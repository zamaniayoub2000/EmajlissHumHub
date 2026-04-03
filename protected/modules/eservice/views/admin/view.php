<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use humhub\modules\eservice\models\EServiceRequest;
use humhub\modules\eservice\assets\EServiceAsset;

EServiceAsset::register($this);

/* @var $this yii\web\View */
/* @var $model \humhub\modules\eservice\models\EServiceRequest */

$this->title = 'Demande #' . $model->id . ' - Administration';
$user = $model->user;
?>

<div class="container">
    <!-- Navigation -->
    <div class="es-nav">
        <a href="<?= Url::to(['/eservice/admin/index']) ?>" class="es-back-btn">
            <i class="fa fa-arrow-left"></i> Retour &agrave; la liste
        </a>
    </div>

    <!-- Header with type label and status badge -->
    <div class="es-view">
        <div class="es-view-header">
            <h2>
                <i class="fa fa-file-alt"></i>
                Demande #<?= Html::encode($model->id) ?> &mdash; <?= Html::encode($model->getTypeLabel()) ?>
                <?php if ($model->sub_type): ?>
                    &mdash; <?= Html::encode($model->getSubTypeLabel()) ?>
                <?php endif; ?>
            </h2>
            <span class="es-badge es-badge-<?= Html::encode($model->status) ?>">
                <?= Html::encode($model->getStatusLabel()) ?>
            </span>
        </div>
    </div>

    <!-- Two-column layout -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 24px;">

        <!-- Left Column (2/3): Request Details -->
        <div>
            <!-- Demandeur Info Card -->
            <div class="es-form" style="margin-bottom: 24px;">
                <div class="es-form-header" style="padding: 16px 28px;">
                    <h2 style="font-size: 15px;"><i class="fa fa-user"></i> Informations du demandeur</h2>
                </div>
                <div class="es-form-body" style="padding: 20px 28px;">
                    <div class="es-info-grid">
                        <div class="es-info-item">
                            <div class="es-info-label">Nom complet</div>
                            <div class="es-info-value">
                                <?php if ($user): ?>
                                    <?= Html::encode(($user->profile->firstname ?? '') . ' ' . ($user->profile->lastname ?? '')) ?>
                                <?php else: ?>
                                    <span style="color: #999;">Utilisateur supprim&eacute;</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="es-info-item">
                            <div class="es-info-label">Email</div>
                            <div class="es-info-value">
                                <?php if ($user): ?>
                                    <?= Html::encode($user->email ?? $user->username) ?>
                                <?php else: ?>
                                    <span style="color: #999;">N/A</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Details Card -->
            <div class="es-form" style="margin-bottom: 24px;">
                <div class="es-form-header" style="padding: 16px 28px;">
                    <h2 style="font-size: 15px;"><i class="fa fa-info-circle"></i> D&eacute;tails de la demande</h2>
                </div>
                <div class="es-form-body" style="padding: 20px 28px;">
                    <div class="es-info-grid">
                        <div class="es-info-item">
                            <div class="es-info-label">Num&eacute;ro de demande</div>
                            <div class="es-info-value">#<?= Html::encode($model->id) ?></div>
                        </div>

                        <div class="es-info-item">
                            <div class="es-info-label">Type de demande</div>
                            <div class="es-info-value">
                                <span class="es-badge" style="background: rgba(109,26,54,0.1); color: #6D1A36;">
                                    <?= Html::encode($model->getTypeLabel()) ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($model->sub_type): ?>
                            <div class="es-info-item">
                                <div class="es-info-label">Sous-type</div>
                                <div class="es-info-value"><?= Html::encode($model->getSubTypeLabel()) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($model->event_name): ?>
                            <div class="es-info-item">
                                <div class="es-info-label">Manifestation / &Eacute;v&eacute;nement</div>
                                <div class="es-info-value"><?= Html::encode($model->event_name) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($model->date_start): ?>
                            <div class="es-info-item">
                                <div class="es-info-label">
                                    <?= $model->type === EServiceRequest::TYPE_HEBERGEMENT ? 'Date d\'arriv&eacute;e' : 'Date de d&eacute;but' ?>
                                </div>
                                <div class="es-info-value"><?= Yii::$app->formatter->asDate($model->date_start, 'long') ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($model->date_end): ?>
                            <div class="es-info-item">
                                <div class="es-info-label">
                                    <?= $model->type === EServiceRequest::TYPE_HEBERGEMENT ? 'Date de d&eacute;part' : 'Date de fin' ?>
                                </div>
                                <div class="es-info-value"><?= Yii::$app->formatter->asDate($model->date_end, 'long') ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="es-info-item">
                            <div class="es-info-label">Statut actuel</div>
                            <div class="es-info-value">
                                <span class="es-badge es-badge-<?= Html::encode($model->status) ?>">
                                    <?= Html::encode($model->getStatusLabel()) ?>
                                </span>
                            </div>
                        </div>

                        <div class="es-info-item">
                            <div class="es-info-label">Date de cr&eacute;ation</div>
                            <div class="es-info-value"><?= Yii::$app->formatter->asDatetime($model->created_at, 'medium') ?></div>
                        </div>

                        <?php if ($model->updated_at): ?>
                            <div class="es-info-item">
                                <div class="es-info-label">Derni&egrave;re modification</div>
                                <div class="es-info-value"><?= Yii::$app->formatter->asDatetime($model->updated_at, 'medium') ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Flight plan (billet_avion) -->
                    <?php if ($model->flight_plan): ?>
                        <div class="es-info-item" style="margin-top: 12px;">
                            <div class="es-info-label">Plan de vol</div>
                            <div class="es-info-value" style="white-space: pre-wrap;"><?= Html::encode($model->flight_plan) ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Shuttle options (billet_avion) -->
                    <?php if ($model->type === EServiceRequest::TYPE_BILLET_AVION): ?>
                        <div class="es-info-grid" style="margin-top: 12px;">
                            <div class="es-info-item">
                                <div class="es-info-label">Navette &agrave; l'arriv&eacute;e</div>
                                <div class="es-info-value">
                                    <?= $model->shuttle_arrival
                                        ? '<i class="fa fa-check-circle" style="color:#28a745"></i> Oui'
                                        : '<i class="fa fa-times-circle" style="color:#dc3545"></i> Non' ?>
                                </div>
                            </div>
                            <div class="es-info-item">
                                <div class="es-info-label">Navette au d&eacute;part</div>
                                <div class="es-info-value">
                                    <?= $model->shuttle_departure
                                        ? '<i class="fa fa-check-circle" style="color:#28a745"></i> Oui'
                                        : '<i class="fa fa-times-circle" style="color:#dc3545"></i> Non' ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Observations -->
                    <?php if ($model->observations): ?>
                        <div class="es-info-item" style="margin-top: 12px;">
                            <div class="es-info-label">Observations</div>
                            <div class="es-info-value" style="white-space: pre-wrap;"><?= Html::encode($model->observations) ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Extra data for document types -->
                    <?php
                    $extraData = $model->extra_data;
                    if (is_string($extraData)) {
                        $extraData = json_decode($extraData, true);
                    }
                    if (!empty($extraData) && is_array($extraData)):
                    ?>
                        <div style="margin-top: 20px; padding-top: 16px; border-top: 2px solid #f0f0f0;">
                            <div class="es-info-label" style="margin-bottom: 12px; font-size: 13px;">
                                <i class="fa fa-folder-open"></i> Donn&eacute;es suppl&eacute;mentaires du document
                            </div>
                            <div class="es-info-grid">
                                <?php
                                $extraLabels = [
                                    'titre_ouvrage' => 'Titre de l\'ouvrage',
                                    'auteur' => 'Auteur',
                                    'numero_bulletin' => 'Num&eacute;ro du Bulletin',
                                    'date_publication' => 'Date de publication',
                                    'theme_dossier' => 'Th&egrave;me du dossier',
                                    'description' => 'Description',
                                    'titre_propose' => 'Titre propos&eacute;',
                                    'editeur' => '&Eacute;diteur',
                                ];
                                foreach ($extraData as $key => $value):
                                    if (empty($value)) continue;
                                ?>
                                    <div class="es-info-item">
                                        <div class="es-info-label"><?= isset($extraLabels[$key]) ? $extraLabels[$key] : Html::encode($key) ?></div>
                                        <div class="es-info-value"><?= Html::encode($value) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Attached Files -->
                    <?php if (!empty($model->files)): ?>
                        <div class="es-file-list">
                            <h4><i class="fa fa-paperclip"></i> Pi&egrave;ces jointes</h4>
                            <?php foreach ($model->files as $file): ?>
                                <div class="es-file-item">
                                    <?php
                                    $icon = 'fa-file';
                                    if (strpos($file->mime_type, 'pdf') !== false) $icon = 'fa-file-pdf';
                                    elseif (strpos($file->mime_type, 'image') !== false) $icon = 'fa-file-image';
                                    elseif (strpos($file->mime_type, 'word') !== false || strpos($file->mime_type, 'document') !== false) $icon = 'fa-file-word';
                                    elseif (strpos($file->mime_type, 'excel') !== false || strpos($file->mime_type, 'spreadsheet') !== false) $icon = 'fa-file-excel';
                                    ?>
                                    <i class="fa <?= $icon ?>"></i>
                                    <a href="<?= Html::encode($file->getDownloadUrl()) ?>">
                                        <?= Html::encode($file->original_name) ?>
                                    </a>
                                    <span class="es-file-size">
                                        <?= Yii::$app->formatter->asShortSize($file->file_size) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Admin Comment -->
                    <?php if ($model->admin_comment): ?>
                        <div class="es-admin-comment">
                            <h4><i class="fa fa-comment-alt"></i> Commentaire de l'administrateur</h4>
                            <p><?= Html::encode($model->admin_comment) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column (1/3): Status Change + History -->
        <div>
            <!-- Status Change Card -->
            <div class="es-form" style="margin-bottom: 24px;">
                <div class="es-form-header" style="padding: 16px 28px;">
                    <h2 style="font-size: 15px;"><i class="fa fa-exchange-alt"></i> Changer le statut</h2>
                </div>
                <div class="es-form-body" style="padding: 20px 28px;">
                    <!-- Current status display -->
                    <div class="es-info-item" style="margin-bottom: 20px;">
                        <div class="es-info-label">Statut actuel</div>
                        <div class="es-info-value">
                            <span class="es-badge es-badge-<?= Html::encode($model->status) ?>">
                                <?= Html::encode($model->getStatusLabel()) ?>
                            </span>
                        </div>
                    </div>

                    <form method="post" action="<?= Url::to(['/eservice/admin/update-status', 'id' => $model->id]) ?>">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                        <div class="form-group">
                            <label class="control-label">Nouveau statut</label>
                            <select name="new_status" class="form-control" required>
                                <option value="">-- S&eacute;lectionner --</option>
                                <?php foreach (EServiceRequest::getStatusesList() as $statusKey => $statusLabel): ?>
                                    <option value="<?= Html::encode($statusKey) ?>"
                                        <?= $statusKey === $model->status ? 'disabled' : '' ?>>
                                        <?= Html::encode($statusLabel) ?>
                                        <?= $statusKey === $model->status ? '(actuel)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="control-label">Commentaire</label>
                            <textarea name="comment" class="form-control" rows="4"
                                      placeholder="Ajouter un commentaire (optionnel)..."></textarea>
                        </div>

                        <button type="submit" class="es-btn-primary" style="width: 100%; justify-content: center;"
                                onclick="return confirm('Confirmer la mise &agrave; jour du statut ?');">
                            <i class="fa fa-save"></i> Mettre &agrave; jour
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <?php if ($model->status === EServiceRequest::STATUS_PENDING || $model->status === EServiceRequest::STATUS_IN_PROGRESS): ?>
                <div class="es-form" style="margin-bottom: 24px;">
                    <div class="es-form-header" style="padding: 16px 28px;">
                        <h2 style="font-size: 15px;"><i class="fa fa-bolt"></i> Actions rapides</h2>
                    </div>
                    <div class="es-form-body" style="padding: 20px 28px;">
                        <div style="display: flex; gap: 10px;">
                            <form method="post" action="<?= Url::to(['/eservice/admin/update-status', 'id' => $model->id]) ?>" style="flex: 1;">
                                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                <?= Html::hiddenInput('new_status', EServiceRequest::STATUS_APPROVED) ?>
                                <button type="submit" class="es-btn-primary" style="width: 100%; justify-content: center; background: #28a745;"
                                        onclick="return confirm('Confirmer l\'approbation ?');">
                                    <i class="fa fa-check"></i> Approuver
                                </button>
                            </form>
                            <form method="post" action="<?= Url::to(['/eservice/admin/update-status', 'id' => $model->id]) ?>" style="flex: 1;">
                                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                <?= Html::hiddenInput('new_status', EServiceRequest::STATUS_REJECTED) ?>
                                <button type="submit" class="es-btn-primary" style="width: 100%; justify-content: center; background: #dc3545;"
                                        onclick="return confirm('Confirmer le rejet ?');">
                                    <i class="fa fa-times"></i> Rejeter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Status History Timeline Card -->
            <div class="es-form">
                <div class="es-form-header" style="padding: 16px 28px;">
                    <h2 style="font-size: 15px;"><i class="fa fa-history"></i> Historique des statuts</h2>
                </div>
                <div class="es-form-body" style="padding: 20px 28px;">
                    <?php if (!empty($model->statusLogs)): ?>
                        <div class="es-timeline" style="margin-top: 0;">
                            <?php foreach ($model->statusLogs as $log): ?>
                                <div class="es-timeline-item">
                                    <div class="es-timeline-date">
                                        <?= Yii::$app->formatter->asDatetime($log->created_at, 'medium') ?>
                                    </div>
                                    <div class="es-timeline-content">
                                        <?php if ($log->old_status): ?>
                                            <span class="es-badge es-badge-<?= Html::encode($log->old_status) ?>" style="font-size: 10px;">
                                                <?= Html::encode(EServiceRequest::getStatusesList()[$log->old_status] ?? $log->old_status) ?>
                                            </span>
                                            <i class="fa fa-arrow-right" style="margin: 0 6px; color: #ccc; font-size: 11px;"></i>
                                        <?php endif; ?>
                                        <span class="es-badge es-badge-<?= Html::encode($log->new_status) ?>" style="font-size: 10px;">
                                            <?= Html::encode(EServiceRequest::getStatusesList()[$log->new_status] ?? $log->new_status) ?>
                                        </span>
                                        <?php if ($log->comment): ?>
                                            <div style="margin-top: 6px; font-size: 13px; color: #666; font-style: italic;">
                                                &laquo; <?= Html::encode($log->comment) ?> &raquo;
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($log->changedByUser): ?>
                                        <div class="es-timeline-user">
                                            <i class="fa fa-user"></i> <?= Html::encode($log->changedByUser->displayName) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="es-text-center" style="padding: 20px 0;">
                            <i class="fa fa-info-circle" style="color: #ccc; font-size: 24px; margin-bottom: 8px; display: block;"></i>
                            <p style="color: #999; font-size: 13px; margin: 0;">Aucun changement de statut enregistr&eacute;</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Responsive override for two-column layout on mobile -->
<style>
    @media (max-width: 992px) {
        .container > div[style*="grid-template-columns: 2fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
