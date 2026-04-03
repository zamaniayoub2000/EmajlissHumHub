<?php

use humhub\modules\externalHtmlStream\models\MajlissPost;
use yii\helpers\Html;

/**
 * Vue stream pour les posts WordPress Majliss synchronisés.
 *
 * @var \humhub\modules\ui\view\components\View $this
 * @var MajlissPost $model
 */
?>

<div class="majliss-stream-entry" data-wp-id="<?= $model->wp_post_id ?>">

    <!-- Image miniature -->
    <?php if (!empty($model->image_url)): ?>
        <div class="majliss-post-image" style="margin-bottom: 12px;">
            <img src="<?= htmlspecialchars($model->image_url, ENT_QUOTES, 'UTF-8') ?>"
                 alt="<?= htmlspecialchars($model->title, ENT_QUOTES, 'UTF-8') ?>"
                 style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 8px;"
                 loading="lazy">
        </div>
    <?php endif; ?>

    <!-- Métadonnées : catégorie + date -->
    <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <?php if (!empty($model->category)): ?>
            <span class="label label-primary">
                <i class="fa fa-folder-o"></i>
                <?= Html::encode($model->category) ?>
            </span>
        <?php endif; ?>

        <?php if (!empty($model->wp_date)): ?>
            <small class="text-muted">
                <i class="fa fa-calendar"></i>
                <?= date('d/m/Y à H:i', strtotime($model->wp_date)) ?>
            </small>
        <?php endif; ?>

        <small class="text-muted">
            <i class="fa fa-wordpress"></i>
            Journal du Conseil
        </small>
    </div>

    <!-- Titre -->
    <h4 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 600; color: #333;">
        <?= Html::encode($model->title) ?>
    </h4>

    <!-- Contenu texte -->
    <?php if (!empty($model->content)): ?>
        <div class="majliss-post-text" style="line-height: 1.6; color: #555;">
            <?php
            $text = htmlspecialchars($model->content, ENT_QUOTES, 'UTF-8');
            $text = nl2br($text);

            // Tronquer à 500 caractères avec "Lire la suite"
            $fullText = strip_tags($model->content);
            if (mb_strlen($fullText) > 500):
            ?>
                <div class="majliss-text-preview">
                    <?= nl2br(htmlspecialchars(mb_substr($fullText, 0, 500), ENT_QUOTES, 'UTF-8')) ?>...
                    <br>
                    <a href="#" class="majliss-read-more" style="color: #667eea; font-weight: 500;"
                       onclick="$(this).closest('.majliss-text-preview').hide().next('.majliss-text-full').show(); return false;">
                        <i class="fa fa-angle-down"></i> Lire la suite
                    </a>
                </div>
                <div class="majliss-text-full" style="display: none;">
                    <?= $text ?>
                    <br>
                    <a href="#" style="color: #667eea; font-weight: 500;"
                       onclick="$(this).closest('.majliss-text-full').hide().prev('.majliss-text-preview').show(); return false;">
                        <i class="fa fa-angle-up"></i> Réduire
                    </a>
                </div>
            <?php else: ?>
                <?= $text ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Badge source -->
    <div style="margin-top: 12px; padding-top: 8px; border-top: 1px solid #f0f0f0;">
        <small class="text-muted">
            <i class="fa fa-exchange"></i>
            Synchronisé depuis Majliss
            <?php if ($model->synced_at): ?>
                &middot; <?= Yii::$app->formatter->asRelativeTime($model->synced_at) ?>
            <?php endif; ?>
        </small>
    </div>
</div>
