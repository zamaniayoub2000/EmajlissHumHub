<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use humhub\modules\eservice\models\EServiceRequest;
use humhub\modules\eservice\assets\EServiceAsset;

EServiceAsset::register($this);
$this->title = 'Administration E-Services';

/* @var $this yii\web\View */
/* @var $searchModel \humhub\modules\eservice\models\EServiceRequestSearch */
/* @var $dataProvider \yii\data\ActiveDataProvider */

// Compute stats
$allModels = EServiceRequest::find();
$totalCount = (clone $allModels)->count();
$pendingCount = (clone $allModels)->andWhere(['status' => EServiceRequest::STATUS_PENDING])->count();
$inProgressCount = (clone $allModels)->andWhere(['status' => EServiceRequest::STATUS_IN_PROGRESS])->count();
$approvedCount = (clone $allModels)->andWhere(['status' => EServiceRequest::STATUS_APPROVED])->count();
$rejectedCount = (clone $allModels)->andWhere(['status' => EServiceRequest::STATUS_REJECTED])->count();
?>

<div class="container">
    <!-- Header -->
    <div class="es-header">
        <h1><i class="fa fa-cogs"></i> ADMINISTRATION E-SERVICES</h1>
        <p>Gestion et suivi de toutes les demandes de services</p>
    </div>

    <!-- Stats Cards -->
    <div class="es-cards-grid" style="grid-template-columns: repeat(5, 1fr);">
        <div class="es-card es-card--bordeaux" style="padding: 20px 14px;">
            <div class="es-card-icon" style="width: 52px; height: 52px; font-size: 22px;">
                <i class="fa fa-folder-open"></i>
            </div>
            <div class="es-card-title" style="min-height: auto; font-size: 24px; color: #6D1A36;">
                <?= Html::encode($totalCount) ?>
            </div>
            <div class="es-card-desc" style="flex-grow: 0; margin-bottom: 0;">Total demandes</div>
        </div>

        <div class="es-card" style="padding: 20px 14px; border-top-color: #ffc107;">
            <div class="es-card-icon" style="width: 52px; height: 52px; font-size: 22px; background: rgba(255,193,7,0.1); color: #856404;">
                <i class="fa fa-clock"></i>
            </div>
            <div class="es-card-title" style="min-height: auto; font-size: 24px; color: #856404;">
                <?= Html::encode($pendingCount) ?>
            </div>
            <div class="es-card-desc" style="flex-grow: 0; margin-bottom: 0;">En attente</div>
        </div>

        <div class="es-card" style="padding: 20px 14px; border-top-color: #17a2b8;">
            <div class="es-card-icon" style="width: 52px; height: 52px; font-size: 22px; background: rgba(23,162,184,0.1); color: #0C5460;">
                <i class="fa fa-spinner"></i>
            </div>
            <div class="es-card-title" style="min-height: auto; font-size: 24px; color: #0C5460;">
                <?= Html::encode($inProgressCount) ?>
            </div>
            <div class="es-card-desc" style="flex-grow: 0; margin-bottom: 0;">En cours</div>
        </div>

        <div class="es-card" style="padding: 20px 14px; border-top-color: #28a745;">
            <div class="es-card-icon" style="width: 52px; height: 52px; font-size: 22px; background: rgba(40,167,69,0.1); color: #155724;">
                <i class="fa fa-check-circle"></i>
            </div>
            <div class="es-card-title" style="min-height: auto; font-size: 24px; color: #155724;">
                <?= Html::encode($approvedCount) ?>
            </div>
            <div class="es-card-desc" style="flex-grow: 0; margin-bottom: 0;">Approuv&eacute;es</div>
        </div>

        <div class="es-card" style="padding: 20px 14px; border-top-color: #dc3545;">
            <div class="es-card-icon" style="width: 52px; height: 52px; font-size: 22px; background: rgba(220,53,69,0.1); color: #721C24;">
                <i class="fa fa-times-circle"></i>
            </div>
            <div class="es-card-title" style="min-height: auto; font-size: 24px; color: #721C24;">
                <?= Html::encode($rejectedCount) ?>
            </div>
            <div class="es-card-desc" style="flex-grow: 0; margin-bottom: 0;">Rejet&eacute;es</div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="es-form" style="margin-bottom: 24px;">
        <div class="es-form-header" style="padding: 16px 28px;">
            <h2 style="font-size: 15px;"><i class="fa fa-filter"></i> Filtres de recherche</h2>
        </div>
        <div class="es-form-body" style="padding: 20px 28px;">
            <?php $form = ActiveForm::begin([
                'action' => Url::to(['/eservice/admin/index']),
                'method' => 'get',
                'options' => ['class' => 'es-filter-form'],
            ]); ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; align-items: end;">
                <!-- Type Filter -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="control-label">Type</label>
                    <?= Html::activeDropDownList($searchModel, 'type', EServiceRequest::getTypesList(), [
                        'class' => 'form-control',
                        'prompt' => '-- Tous les types --',
                    ]) ?>
                </div>

                <!-- Status Filter -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="control-label">Statut</label>
                    <?= Html::activeDropDownList($searchModel, 'status', EServiceRequest::getStatusesList(), [
                        'class' => 'form-control',
                        'prompt' => '-- Tous les statuts --',
                    ]) ?>
                </div>

                <!-- Date From -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="control-label">Date du</label>
                    <?= Html::activeInput('date', $searchModel, 'date_from', [
                        'class' => 'form-control',
                    ]) ?>
                </div>

                <!-- Date To -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="control-label">Date au</label>
                    <?= Html::activeInput('date', $searchModel, 'date_to', [
                        'class' => 'form-control',
                    ]) ?>
                </div>

                <!-- Buttons -->
                <div class="form-group" style="margin-bottom: 0; display: flex; gap: 8px;">
                    <button type="submit" class="es-btn-primary" style="flex: 1;">
                        <i class="fa fa-search"></i> Rechercher
                    </button>
                    <a href="<?= Url::to(['/eservice/admin/index']) ?>" class="es-btn-secondary" style="flex: 1; text-align: center; justify-content: center;">
                        <i class="fa fa-undo"></i> R&eacute;initialiser
                    </a>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <!-- Results Table -->
    <div class="es-dashboard">
        <div class="es-dashboard-header">
            <h2><i class="fa fa-list"></i> R&eacute;sultats (<?= Html::encode($dataProvider->getTotalCount()) ?>)</h2>
            <a href="<?= Url::to(array_merge(['/eservice/admin/export'], Yii::$app->request->queryParams)) ?>" class="es-btn-secondary">
                <i class="fa fa-download"></i> Exporter CSV
            </a>
        </div>

        <?php if ($dataProvider->getTotalCount() > 0): ?>
            <div class="es-table-responsive">
                <table class="es-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Type</th>
                            <th>&Eacute;v&eacute;nement</th>
                            <th>Date cr&eacute;ation</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataProvider->getModels() as $model): ?>
                            <?php $user = $model->user; ?>
                            <tr>
                                <td><strong>#<?= Html::encode($model->id) ?></strong></td>
                                <td>
                                    <?php if ($user): ?>
                                        <strong><?= Html::encode($user->profile->firstname ?? '') ?> <?= Html::encode($user->profile->lastname ?? '') ?></strong>
                                        <br><small style="color: #999;"><?= Html::encode($user->email ?? $user->username) ?></small>
                                    <?php else: ?>
                                        <span style="color: #999;">Utilisateur supprim&eacute;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="es-badge" style="background: rgba(109,26,54,0.1); color: #6D1A36;">
                                        <?= Html::encode($model->getTypeLabel()) ?>
                                    </span>
                                    <?php if ($model->sub_type): ?>
                                        <br><small style="color: #999; margin-top: 4px; display: inline-block;">
                                            <?= Html::encode($model->getSubTypeLabel()) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= Html::encode($model->event_name ?: '-') ?></td>
                                <td><?= Yii::$app->formatter->asDatetime($model->created_at, 'medium') ?></td>
                                <td>
                                    <span class="es-badge es-badge-<?= Html::encode($model->status) ?>">
                                        <?= Html::encode($model->getStatusLabel()) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        <a href="<?= Url::to(['/eservice/admin/view', 'id' => $model->id]) ?>" class="es-btn-view">
                                            <i class="fa fa-eye"></i> Voir
                                        </a>

                                        <?php if ($model->status === EServiceRequest::STATUS_PENDING || $model->status === EServiceRequest::STATUS_IN_PROGRESS): ?>
                                            <form method="post" action="<?= Url::to(['/eservice/admin/update-status', 'id' => $model->id]) ?>" style="display: inline;">
                                                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                                <?= Html::hiddenInput('new_status', EServiceRequest::STATUS_APPROVED) ?>
                                                <button type="submit" class="es-btn-view" style="background: rgba(40,167,69,0.1); color: #155724; border: none; cursor: pointer;"
                                                        onclick="return confirm('Confirmer l\'approbation de cette demande ?');">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="post" action="<?= Url::to(['/eservice/admin/update-status', 'id' => $model->id]) ?>" style="display: inline;">
                                                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                                                <?= Html::hiddenInput('new_status', EServiceRequest::STATUS_REJECTED) ?>
                                                <button type="submit" class="es-btn-view" style="background: rgba(220,53,69,0.1); color: #721C24; border: none; cursor: pointer;"
                                                        onclick="return confirm('Confirmer le rejet de cette demande ?');">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div style="padding: 16px 24px; text-align: center;">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'options' => ['class' => 'pagination'],
                ]) ?>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="es-empty-state">
                <div class="es-empty-state-icon">
                    <i class="fa fa-inbox"></i>
                </div>
                <h3>Aucune demande trouv&eacute;e</h3>
                <p>Aucune demande ne correspond aux crit&egrave;res de recherche.</p>
                <a href="<?= Url::to(['/eservice/admin/index']) ?>" class="es-btn-primary">
                    <i class="fa fa-undo"></i> R&eacute;initialiser les filtres
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
