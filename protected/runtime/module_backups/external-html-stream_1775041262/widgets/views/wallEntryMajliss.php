<?php

use humhub\modules\externalHtmlStream\models\MajlissPost;
use humhub\modules\externalHtmlStream\assets\ExternalHtmlStreamAsset;
use yii\helpers\Html;

/**
 * Vue stream pour les posts WordPress Majliss synchronisés.
 * Design type carte article professionnelle.
 *
 * @var \humhub\modules\ui\view\components\View $this
 * @var MajlissPost $model
 */

ExternalHtmlStreamAsset::register($this);

$uniqueId = 'majliss-post-' . $model->id;
$postContent = $model->post_content ?? '';
$fullText = strip_tags($postContent);
$isLong = mb_strlen($fullText) > 300;
$excerpt = $isLong ? mb_substr($fullText, 0, 300) : $fullText;
$wpDate = !empty($model->wp_date) ? date('d M Y', strtotime($model->wp_date)) : '';
$wpTime = !empty($model->wp_date) ? date('H:i', strtotime($model->wp_date)) : '';
?>

<div class="majliss-card" id="<?= $uniqueId ?>" data-wp-id="<?= $model->wp_post_id ?>">

    <!-- ═══ CATÉGORIE BADGE ═══ -->
    <?php if (!empty($model->category)): ?>
        <div class="majliss-card-badge">
            <span class="majliss-category">
                <?= Html::encode($model->category) ?>
            </span>
        </div>
    <?php endif; ?>

    <!-- ═══ IMAGE À LA UNE ═══ -->
    <?php if (!empty($model->image_url)): ?>
        <div class="majliss-card-image">
            <img src="<?= htmlspecialchars($model->image_url, ENT_QUOTES, 'UTF-8') ?>"
                 alt="<?= htmlspecialchars($model->title, ENT_QUOTES, 'UTF-8') ?>"
                 loading="lazy"
                 onerror="this.closest('.majliss-card-image').style.display='none'">
            <!-- Overlay gradient -->
            <div class="majliss-image-overlay"></div>
        </div>
    <?php endif; ?>

    <!-- ═══ CORPS DE LA CARTE ═══ -->
    <div class="majliss-card-body">

        <!-- Titre -->
        <h3 class="majliss-card-title">
            <?= Html::encode($model->title) ?>
        </h3>

        <!-- Métadonnées -->
        <div class="majliss-card-meta">
            <?php if (!empty($wpDate)): ?>
                <span class="majliss-meta-item">
                    <i class="fa fa-calendar-o"></i> <?= $wpDate ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($wpTime) && $wpTime !== '00:00'): ?>
                <span class="majliss-meta-item">
                    <i class="fa fa-clock-o"></i> <?= $wpTime ?>
                </span>
            <?php endif; ?>
            <span class="majliss-meta-item majliss-meta-source">
                <i class="fa fa-wordpress"></i> Majliss
            </span>
        </div>

        <!-- Séparateur -->
        <div class="majliss-card-divider"></div>

        <!-- Contenu texte -->
        <?php if (!empty($fullText)): ?>
            <div class="majliss-card-content">

                <?php if ($isLong): ?>
                    <!-- Version tronquée -->
                    <div class="majliss-text-preview" id="<?= $uniqueId ?>-preview">
                        <p><?= nl2br(htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8')) ?><span class="majliss-ellipsis">...</span></p>
                        <button type="button" class="majliss-btn-more" onclick="majlissToggle('<?= $uniqueId ?>', true)">
                            <i class="fa fa-chevron-down"></i> Afficher plus
                        </button>
                    </div>

                    <!-- Version complète -->
                    <div class="majliss-text-full" id="<?= $uniqueId ?>-full" style="display: none;">
                        <p><?= nl2br(htmlspecialchars($fullText, ENT_QUOTES, 'UTF-8')) ?></p>
                        <button type="button" class="majliss-btn-less" onclick="majlissToggle('<?= $uniqueId ?>', false)">
                            <i class="fa fa-chevron-up"></i> Réduire
                        </button>
                    </div>
                <?php else: ?>
                    <p><?= nl2br(htmlspecialchars($fullText, ENT_QUOTES, 'UTF-8')) ?></p>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </div>

    <!-- ═══ FOOTER ═══ -->
    <div class="majliss-card-footer">
        <div class="majliss-footer-left">
            <span class="majliss-sync-badge">
                <i class="fa fa-check-circle"></i> Synchronisé
            </span>
            <?php if ($model->synced_at): ?>
                <span class="majliss-sync-time">
                    <?= Yii::$app->formatter->asRelativeTime($model->synced_at) ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="majliss-footer-right">
            <span class="majliss-wp-id">WP #<?= $model->wp_post_id ?></span>
        </div>
    </div>
</div>
