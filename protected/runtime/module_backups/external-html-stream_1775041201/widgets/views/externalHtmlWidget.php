<?php

use humhub\modules\externalHtmlStream\models\ExternalPost;
use yii\helpers\Html;

/**
 * @var \humhub\modules\ui\view\components\View $this
 * @var ExternalPost $model
 * @var bool $showTitle
 * @var bool $showRefreshButton
 * @var bool $useIframe
 * @var string|null $maxHeight
 */
?>

<div class="external-html-widget card" style="border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; margin-bottom: 15px;">
    <?php if ($showTitle): ?>
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 12px 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <i class="fa fa-globe"></i>
                    <strong><?= Html::encode($model->title) ?></strong>
                </div>
                <?php if ($model->last_fetched_at): ?>
                    <small style="opacity: 0.8;">
                        <i class="fa fa-clock-o"></i>
                        <?= Yii::$app->formatter->asRelativeTime($model->last_fetched_at) ?>
                    </small>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card-body" style="padding: 16px; <?= $maxHeight ? "max-height: {$maxHeight}; overflow-y: auto;" : '' ?>">
        <?php if ($useIframe): ?>
            <?php $iframeContent = htmlspecialchars($model->getDisplayHtml(), ENT_QUOTES, 'UTF-8'); ?>
            <iframe
                sandbox="allow-same-origin"
                srcdoc="<?= $iframeContent ?>"
                style="width: 100%; border: none; min-height: 200px;"
                onload="this.style.height = this.contentWindow.document.body.scrollHeight + 'px';"
            ></iframe>
        <?php else: ?>
            <div class="external-html-rendered">
                <?= $model->getDisplayHtml() ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($showRefreshButton): ?>
        <div class="card-footer" style="padding: 8px 16px; background: #f8f9fa; border-top: 1px solid #e0e0e0; text-align: right;">
            <button type="button"
                    class="btn btn-xs btn-default btn-refresh-external"
                    data-post-id="<?= $model->id ?>">
                <i class="fa fa-refresh"></i>
                <?= Yii::t('ExternalHtmlStreamModule.base', 'Rafraîchir') ?>
            </button>
        </div>
    <?php endif; ?>
</div>
