<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\modules\eservice\models\EServiceRequest;

/** @var yii\web\View $this */
/** @var EServiceRequest $model */

\humhub\modules\eservice\assets\EServiceAsset::register($this);
$this->title = 'Demande #' . $model->id;
?>

<div class="container">
    <!-- Navigation -->
    <div class="es-nav">
        <a href="<?= Url::to(['/eservice/index/dashboard']) ?>" class="es-back-btn">
            <i class="fa fa-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>

    <!-- View Card -->
    <div class="es-view">
        <div class="es-view-header">
            <h2>
                <i class="fa fa-file-alt"></i>
                <?= Html::encode($model->getTypeLabel()) ?>
                <?php if ($model->sub_type): ?>
                    &mdash; <?= Html::encode($model->getSubTypeLabel()) ?>
                <?php endif; ?>
            </h2>
            <span class="es-badge es-badge-<?= Html::encode($model->status) ?>">
                <?= Html::encode($model->getStatusLabel()) ?>
            </span>
        </div>

        <div class="es-view-body">
            <!-- Info Grid -->
            <div class="es-info-grid">
                <div class="es-info-item">
                    <div class="es-info-label">Num&eacute;ro de demande</div>
                    <div class="es-info-value">#<?= Html::encode($model->id) ?></div>
                </div>

                <div class="es-info-item">
                    <div class="es-info-label">Type</div>
                    <div class="es-info-value"><?= Html::encode($model->getTypeLabel()) ?></div>
                </div>

                <?php if ($model->sub_type): ?>
                    <div class="es-info-item">
                        <div class="es-info-label">Sous-type</div>
                        <div class="es-info-value"><?= Html::encode($model->getSubTypeLabel()) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($model->event_name): ?>
                    <div class="es-info-item">
                        <div class="es-info-label">Manifestation</div>
                        <div class="es-info-value"><?= Html::encode($model->event_name) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($model->date_start): ?>
                    <div class="es-info-item">
                        <div class="es-info-label"><?= $model->type === 'hebergement' ? 'Date d\'arriv&eacute;e' : 'Date de d&eacute;part' ?></div>
                        <div class="es-info-value"><?= Yii::$app->formatter->asDate($model->date_start, 'long') ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($model->date_end): ?>
                    <div class="es-info-item">
                        <div class="es-info-label"><?= $model->type === 'hebergement' ? 'Date de d&eacute;part' : 'Date de retour' ?></div>
                        <div class="es-info-value"><?= Yii::$app->formatter->asDate($model->date_end, 'long') ?></div>
                    </div>
                <?php endif; ?>

                <div class="es-info-item">
                    <div class="es-info-label">Date de cr&eacute;ation</div>
                    <div class="es-info-value"><?= Yii::$app->formatter->asDatetime($model->created_at, 'medium') ?></div>
                </div>

                <div class="es-info-item">
                    <div class="es-info-label">Statut</div>
                    <div class="es-info-value">
                        <span class="es-badge es-badge-<?= Html::encode($model->status) ?>">
                            <?= Html::encode($model->getStatusLabel()) ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if ($model->flight_plan): ?>
                <div class="es-info-item" style="margin-bottom: 20px;">
                    <div class="es-info-label">Plan de vol</div>
                    <div class="es-info-value" style="white-space: pre-wrap;"><?= Html::encode($model->flight_plan) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($model->type === 'billet_avion'): ?>
                <div class="es-info-grid">
                    <div class="es-info-item">
                        <div class="es-info-label">Navette &agrave; l'arriv&eacute;e</div>
                        <div class="es-info-value">
                            <?= $model->shuttle_arrival ? '<i class="fa fa-check-circle" style="color:#28a745"></i> Oui' : '<i class="fa fa-times-circle" style="color:#dc3545"></i> Non' ?>
                        </div>
                    </div>
                    <div class="es-info-item">
                        <div class="es-info-label">Navette au d&eacute;part</div>
                        <div class="es-info-value">
                            <?= $model->shuttle_departure ? '<i class="fa fa-check-circle" style="color:#28a745"></i> Oui' : '<i class="fa fa-times-circle" style="color:#dc3545"></i> Non' ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($model->observations): ?>
                <div class="es-info-item" style="margin-bottom: 20px;">
                    <div class="es-info-label">Observations</div>
                    <div class="es-info-value" style="white-space: pre-wrap;"><?= Html::encode($model->observations) ?></div>
                </div>
            <?php endif; ?>

            <?php
            // Display extra_data for document sub-types
            $extraData = $model->extra_data;
            if (is_string($extraData)) {
                $extraData = json_decode($extraData, true);
            }
            if (!empty($extraData) && is_array($extraData)):
            ?>
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
            <?php endif; ?>

            <!-- Files -->
            <?php if (!empty($model->files)): ?>
                <div class="es-file-list">
                    <h4><i class="fa fa-paperclip"></i> Pi&egrave;ces jointes</h4>
                    <?php foreach ($model->files as $file): ?>
                        <div class="es-file-item">
                            <?php
                            $icon = 'fa-file';
                            if (strpos($file->mime_type, 'pdf') !== false) $icon = 'fa-file-pdf';
                            elseif (strpos($file->mime_type, 'image') !== false) $icon = 'fa-file-image';
                            ?>
                            <i class="fa <?= $icon ?>"></i>
                            <a href="<?= Url::to(['/eservice/request/download', 'id' => $file->id]) ?>">
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

            <!-- Status Timeline -->
            <?php if (!empty($model->statusLogs)): ?>
                <div class="es-timeline">
                    <h4><i class="fa fa-history"></i> Historique des statuts</h4>
                    <?php foreach ($model->statusLogs as $log): ?>
                        <div class="es-timeline-item">
                            <div class="es-timeline-date">
                                <?= Yii::$app->formatter->asDatetime($log->created_at, 'medium') ?>
                            </div>
                            <div class="es-timeline-content">
                                <?php if ($log->old_status): ?>
                                    <span class="es-badge es-badge-<?= Html::encode($log->old_status) ?>" style="font-size:10px;">
                                        <?= Html::encode(EServiceRequest::getStatusesList()[$log->old_status] ?? $log->old_status) ?>
                                    </span>
                                    <i class="fa fa-arrow-right" style="margin: 0 6px; color: #ccc; font-size: 11px;"></i>
                                <?php endif; ?>
                                <span class="es-badge es-badge-<?= Html::encode($log->new_status) ?>" style="font-size:10px;">
                                    <?= Html::encode(EServiceRequest::getStatusesList()[$log->new_status] ?? $log->new_status) ?>
                                </span>
                                <?php if ($log->comment): ?>
                                    <br><small style="color:#888;"><?= Html::encode($log->comment) ?></small>
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
            <?php endif; ?>
        </div>
    </div>
</div>
