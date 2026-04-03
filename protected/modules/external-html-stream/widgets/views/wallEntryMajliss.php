<?php

use humhub\modules\externalHtmlStream\models\MajlissPost;
use humhub\modules\externalHtmlStream\assets\ExternalHtmlStreamAsset;
use yii\helpers\Html;

/**
 * Vue stream pour les posts WordPress Majliss synchronisés.
 *
 * - Affiche le contenu HTML riche (liens, boutons, iframes, vidéos)
 * - Détecte automatiquement le RTL (arabe) et adapte l'affichage
 * - Utilise data-ui-show-more natif HumHub pour "Afficher la suite"
 *
 * Format : Catégorie badge + date → Titre H2 → Image → Contenu HTML riche
 *
 * @var \humhub\modules\ui\view\components\View $this
 * @var MajlissPost $model
 */

ExternalHtmlStreamAsset::register($this);

$postContent = $model->post_content ?? '';
$wpDate = !empty($model->wp_date) ? date('d M Y', strtotime($model->wp_date)) : '';
$isRtl = $model->isRtl();
$dir = $model->getTextDirection();
$alignClass = $isRtl ? 'majliss-rtl' : 'majliss-ltr';
?>

<div class="majliss-stream-post <?= $alignClass ?>"
     id="majliss-post-<?= $model->id ?>"
     dir="<?= $dir ?>"
     lang="<?= $isRtl ? 'ar' : 'fr' ?>">

    <?php // ═══ CATÉGORIE BADGE + DATE ═══ ?>
    <?php if (!empty($model->category) || !empty($wpDate)): ?>
        <div class="majliss-meta-bar">
            <?php if (!empty($model->category)): ?>
                <span class="majliss-category-badge">
                    <?= Html::encode($model->category) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($wpDate)): ?>
                <span class="majliss-date-label">
                    <i class="fa fa-calendar-o"></i> <?= $wpDate ?>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php // ═══ TITRE H2 ═══ ?>
    <h2 class="majliss-post-title">
        <?= Html::encode($model->title) ?>
    </h2>

    <?php // ═══ CONTENU HTML RICHE avec data-ui-show-more natif HumHub ═══ ?>
    <?php if (!empty($postContent)): ?>
        <div data-ui-show-more
             data-read-more-text="<?= $isRtl ? 'عرض المزيد' : 'Afficher la suite' ?>"
             data-collapse-at="150"
             class="majliss-post-content majliss-rich-html">
            <?php
                // Le contenu est déjà sanitisé par HTMLPurifier dans MajlissSyncService::cleanContent()
                // On l'affiche tel quel pour préserver les liens, iframes, vidéos, boutons, etc.
                echo $postContent;
            ?>
        </div>
    <?php endif; ?>

</div>
