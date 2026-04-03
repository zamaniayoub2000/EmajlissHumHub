<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\modules\eservice\models\EServiceRequest;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

\humhub\modules\eservice\assets\EServiceAsset::register($this);
$this->title = 'Mes Demandes';
?>

<div class="container">
    <!-- Header -->
    <div class="es-header">
        <h1><i class="fa fa-list-alt"></i> MES DEMANDES</h1>
        <p>Suivi de l'ensemble de vos demandes de services</p>
    </div>

    <!-- Navigation -->
    <div class="es-nav">
        <a href="<?= Url::to(['/eservice/index/index']) ?>" class="es-back-btn">
            <i class="fa fa-arrow-left"></i> Retour aux services
        </a>
    </div>

    <!-- Dashboard -->
    <div class="es-dashboard">
        <div class="es-dashboard-header">
            <h2><i class="fa fa-folder-open"></i> Mes Demandes</h2>
            <a href="<?= Url::to(['/eservice/index/index']) ?>" class="es-btn-primary">
                <i class="fa fa-plus"></i> Nouvelle demande
            </a>
        </div>

        <?php if ($dataProvider->getTotalCount() > 0): ?>
            <div class="es-table-responsive">
                <table class="es-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>&Eacute;v&eacute;nement</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dataProvider->getModels() as $model): ?>
                            <tr>
                                <td><strong><?= Html::encode($model->id) ?></strong></td>
                                <td>
                                    <?= Html::encode($model->getTypeLabel()) ?>
                                    <?php if ($model->sub_type): ?>
                                        <br><small style="color:#999"><?= Html::encode($model->getSubTypeLabel()) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= Html::encode($model->event_name ?: '-') ?></td>
                                <td><?= Yii::$app->formatter->asDate($model->created_at, 'medium') ?></td>
                                <td>
                                    <span class="es-badge es-badge-<?= Html::encode($model->status) ?>">
                                        <?= Html::encode($model->getStatusLabel()) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= Url::to(['/eservice/request/view', 'id' => $model->id]) ?>" class="es-btn-view">
                                        <i class="fa fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div style="padding: 16px 24px; text-align: center;">
                <?= \yii\widgets\LinkPager::widget([
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
                <h3>Aucune demande</h3>
                <p>Vous n'avez pas encore soumis de demande de service.</p>
                <a href="<?= Url::to(['/eservice/index/index']) ?>" class="es-btn-primary">
                    <i class="fa fa-plus"></i> Cr&eacute;er une demande
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
