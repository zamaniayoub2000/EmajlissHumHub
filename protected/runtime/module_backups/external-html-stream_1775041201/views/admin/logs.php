<?php

use humhub\modules\externalHtmlStream\models\SyncLog;
use humhub\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \humhub\modules\ui\view\components\View $this
 * @var SyncLog[] $logs
 */

$this->title = 'Logs de synchronisation';
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h4 class="panel-title" style="margin: 0;">
                <i class="fa fa-list-alt"></i>
                Logs de synchronisation Majliss
            </h4>
            <div>
                <?= Button::danger('Nettoyer (+30 jours)')
                    ->link(Url::to(['clear-logs']))
                    ->icon('fa-trash')
                    ->sm()
                    ->options(['data-confirm' => 'Supprimer les logs de plus de 30 jours ?']) ?>
                <?= Button::defaultType('Retour')
                    ->link(Url::to(['index']))
                    ->icon('fa-arrow-left')
                    ->sm() ?>
            </div>
        </div>
    </div>

    <div class="panel-body">
        <?php if (empty($logs)): ?>
            <div class="text-center" style="padding: 30px;">
                <i class="fa fa-check-circle" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 15px;"></i>
                <p class="text-muted">Aucun log pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-condensed table-striped">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Date</th>
                            <th style="width: 60px;">Niveau</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr class="<?= $log->level === 'error' ? 'danger' : ($log->level === 'warn' ? 'warning' : '') ?>">
                                <td>
                                    <small><?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?></small>
                                </td>
                                <td>
                                    <span class="label label-<?= $log->getLevelClass() ?>">
                                        <?= strtoupper($log->level) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= Html::encode($log->message) ?>
                                    <?php if ($log->context): ?>
                                        <br><small class="text-muted"><code><?= Html::encode(mb_substr($log->context, 0, 200)) ?></code></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
