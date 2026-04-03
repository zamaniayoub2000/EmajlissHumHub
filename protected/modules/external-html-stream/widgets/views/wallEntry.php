<?php

use humhub\modules\externalHtmlStream\models\ExternalPost;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \humhub\modules\ui\view\components\View $this
 * @var ExternalPost $model
 */
?>

<div class="external-html-stream-entry" data-post-id="<?= $model->id ?>">
    <!-- En-tête -->
    <div class="external-html-header" style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #eee;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; font-size: 14px;">
                <i class="fa fa-globe"></i>
            </span>
            <div>
                <strong style="font-size: 14px;"><?= Html::encode($model->title) ?></strong>
                <br>
                <small class="text-muted">
                    <i class="fa fa-link"></i>
                    <?= Html::encode(parse_url($model->api_url, PHP_URL_HOST)) ?>
                    <?php if ($model->last_fetched_at): ?>
                        &middot;
                        <i class="fa fa-clock-o"></i>
                        <?= Yii::$app->formatter->asRelativeTime($model->last_fetched_at) ?>
                    <?php endif; ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Contenu HTML externe -->
    <div class="external-html-content"
         id="external-content-<?= $model->id ?>"
         style="position: relative; min-height: 50px;">

        <!-- Loader -->
        <div class="external-html-loader" style="display: none;">
            <i class="fa fa-spinner fa-spin fa-2x" style="color: #667eea;"></i>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">
                <?= Yii::t('ExternalHtmlStreamModule.base', 'Chargement...') ?>
            </p>
        </div>

        <!-- Contenu -->
        <div class="external-html-body" style="overflow: hidden; word-wrap: break-word;">
            <?php if (!empty($model->cached_html)): ?>
                <?= $model->cached_html ?>
            <?php else: ?>
                <div class="alert alert-info" style="margin: 0;">
                    <i class="fa fa-info-circle"></i>
                    <?= Yii::t('ExternalHtmlStreamModule.base', 'Contenu en cours de chargement...') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="external-html-footer" style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #eee; text-align: right;">
        <button type="button"
                class="btn btn-xs btn-default btn-refresh-external"
                data-post-id="<?= $model->id ?>"
                data-refresh-url="<?= Url::to(['/external-html-stream/stream/refresh']) ?>"
                title="<?= Yii::t('ExternalHtmlStreamModule.base', 'Rafraîchir le contenu') ?>">
            <i class="fa fa-refresh"></i>
            <?= Yii::t('ExternalHtmlStreamModule.base', 'Rafraîchir') ?>
        </button>
    </div>
</div>
