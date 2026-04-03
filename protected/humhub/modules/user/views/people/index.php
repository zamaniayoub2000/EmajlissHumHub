<?php
use humhub\assets\CardsAsset;
use humhub\helpers\Html;
use humhub\modules\user\components\PeopleQuery;
use humhub\modules\user\widgets\PeopleCard;
use humhub\modules\user\widgets\PeopleFilters;
use humhub\modules\user\widgets\PeopleHeadingButtons;
use yii\web\View;
/* @var $this View */
/* @var $people PeopleQuery */
CardsAsset::register($this);
?>

<style>
/* ── Hero ─────────────────────────────────────────────────── */
.tc-hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 0 16px;
}

@keyframes lineFadeIn {
    from { opacity: 0; transform: scaleX(0); }
    to   { opacity: 1; transform: scaleX(1); }
}
.tc-hero-line {
    width: 220px;
    height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, transparent, #3c5f8c, transparent);
    margin-top: 14px;
    transform-origin: center;
    animation: lineFadeIn 0.6s cubic-bezier(0.4,0,0.2,1) 0.2s both;
}

@keyframes logoFadeIn {
    from { opacity: 0; transform: translateY(20px) scale(0.98); }
    to   { opacity: 1; transform: translateY(-6px) scale(1); }
}
.tc-logo-wrap {
    display: flex;
    justify-content: center;
    position: relative;
    top: -14px;
    animation: logoFadeIn 0.6s cubic-bezier(0.4,0,0.2,1) both;
}
.tc-logo-wrap img {
    max-height: 100px;
    width: auto;
    object-fit: contain;
    filter: drop-shadow(0 6px 18px rgba(0,0,0,.22));
}

/* ── Cards grid ──────────────────────────────────────────── */
.row.cards {
    margin-top: 8px;
    row-gap: 16px;
}

/* Each col becomes a flex column so the card inside stretches to full row height */
.row.cards > [class*="col-"] {
    display: flex !important;
    flex-direction: column !important;
    align-items: stretch !important;
}

/* The link and card both fill the column height */
.row.cards .tc-people-card-link {
    display: flex !important;
    flex-direction: column !important;
    flex: 1 1 auto !important;
    width: 100% !important;
}
.row.cards .tc-people-card.card-panel {
    flex: 1 1 auto !important;
    width: 100% !important;
}

/* ── Staggered pop-in ─────────────────────────────────────── */
@keyframes cardPopIn {
    from { opacity: 0; transform: translateY(20px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
.row.cards > [class*="col-"]:nth-child(1)  { animation: cardPopIn .45s cubic-bezier(0.4,0,0.2,1) 0.05s both; }
.row.cards > [class*="col-"]:nth-child(2)  { animation: cardPopIn .45s cubic-bezier(0.4,0,0.2,1) 0.10s both; }
.row.cards > [class*="col-"]:nth-child(3)  { animation: cardPopIn .45s cubic-bezier(0.4,0,0.2,1) 0.15s both; }
.row.cards > [class*="col-"]:nth-child(4)  { animation: cardPopIn .45s cubic-bezier(0.4,0,0.2,1) 0.20s both; }
.row.cards > [class*="col-"]:nth-child(5)  { animation: cardPopIn .45s cubic-bezier(0.4,0,0.2,1) 0.25s both; }
.row.cards > [class*="col-"]:nth-child(6)  { animation: cardPopIn .45s cubic-bezier(0.4,0,0.2,1) 0.30s both; }
.row.cards > [class*="col-"]:nth-child(7)  { animation: cardPopIn .45s cubic-bezier(0.4,0,0.2,1) 0.35s both; }
.row.cards > [class*="col-"]:nth-child(8)  { animation: cardPopIn .45s cubic-bezier(0.4,0,0.2,1) 0.40s both; }
.row.cards > [class*="col-"]:nth-child(n+9){ animation: cardPopIn .45s cubic-bezier(0.4,0,0.2,1) 0.45s both; }

@media (prefers-reduced-motion: reduce) {
    .row.cards > [class*="col-"] { animation: none !important; }
}
</style>

<!-- Hero -->
<div class="tc-hero">
    <div class="tc-logo-wrap">
        <img src="<?= Yii::$app->view->theme->getBaseUrl() ?>/img/emajlis.png" alt="Logo">
    </div>
    <div class="tc-hero-line"></div>
</div>

<!-- Filter panel -->
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Yii::t('UserModule.base', '<strong>People</strong>') ?>
        <?= PeopleHeadingButtons::widget() ?>
    </div>
    <div class="panel-body">
        <?= PeopleFilters::widget(['query' => $people]) ?>
    </div>
</div>

<!-- Cards -->
<div class="row cards">
    <?php if (!$people->exists()): ?>
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <strong><?= Yii::t('UserModule.base', 'No results found!'); ?></strong><br/>
                    <?= Yii::t('UserModule.base', 'Try other keywords or remove filters.'); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php foreach ($people->all() as $user) : ?>
        <?= PeopleCard::widget(['user' => $user]); ?>
    <?php endforeach; ?>
</div>

<?php if (!$people->isLastPage()) : ?>
    <?= Html::tag('div', '', [
        'class'             => 'cards-end',
        'data-current-page' => $people->pagination->getPage() + 1,
        'data-total-pages'  => $people->pagination->getPageCount(),
        'data-ui-loader'    => '',
    ]) ?>
<?php endif; ?>