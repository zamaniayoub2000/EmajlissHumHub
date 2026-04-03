<?php
/**
 * ThemeCitoyen override - SpaceDirectoryCard (Enhanced with Status & Metrics)
 * File: /var/www/race/themes/ThemeCitoyen/views/space/widgets/spaceDirectoryCard.php
 * Encoding: UTF-8
 */

use humhub\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image;
use humhub\modules\space\widgets\SpaceDirectoryActionButtons;
use humhub\modules\space\widgets\SpaceDirectoryIcons;
use humhub\modules\space\widgets\SpaceDirectoryStatus;
use humhub\modules\space\widgets\SpaceDirectoryTagList;
use yii\web\View;

/* @var $this View */
/* @var $space Space */

/** Force UTF-8 response (prevents ? when server sends wrong charset) */
try {
    Yii::$app->response->headers->set('Content-Type', 'text/html; charset=UTF-8');
} catch (\Throwable $e) {
    // ignore if headers already sent
}

$hasCover = $space->getProfileBannerImage()->hasImage();
$coverUrl = $hasCover ? $space->getProfileBannerImage()->getUrl() : null;

// Better fallback: subtle gradient if no cover
$fallbackCover = 'linear-gradient(135deg, rgba(165,53,53,.18) 0%, rgba(6,56,59,.18) 55%, rgba(205,133,63,.14) 100%)';

$spaceUrl = $space->getUrl();

// TODO: Replace these with actual data from your database/model
$isActive = true;          // Get from your form status
$responseCount = 0;        // Replace with actual response count
$appreciationCount = 0;    // Replace with actual appreciation/likes count
$participantCount = 0;     // Replace with actual participant count

$statusText  = $isActive ? 'Active' : 'Termin&eacute;e';
$statusClass = $isActive ? 'tc-status-active' : 'tc-status-finished';
?>

<meta charset="utf-8">

<style>
/* ============================================================
   ThemeCitoyen - Premium Professional Space Card
   Elegant, Minimalistic, Modern Design
============================================================ */
.tc-space-card{
  --tc-red:#A53535;
  --tc-dark:#06383B;
  --tc-green:#10b981;
  --tc-blue:#3b82f6;
  --tc-purple:#8b5cf6;
  --tc-orange:#f59e0b;

  --tc-border: rgba(15,23,42,.08);
  --tc-shadow: 0 2px 8px rgba(0,0,0,.04), 0 12px 32px rgba(0,0,0,.06);
  --tc-shadow-hover: 0 8px 24px rgba(0,0,0,.08), 0 24px 56px rgba(0,0,0,.12);

  --tc-title: rgba(17,24,39,.95);
  --tc-muted: rgba(71,85,105,.85);
  --tc-light-muted: rgba(100,116,139,.7);

  --tc-radius: 20px;
  --tc-cover-h: 160px;
}

/* ============================================================
   CARD ANIMATIONS
============================================================ */
@keyframes cardFadeIn {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes shimmer {
  0% { background-position: -1000px 0; }
  100% { background-position: 1000px 0; }
}

@keyframes statusPulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
  }
  50% {
    box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
  }
}

.tc-space-card.card-panel{
  border-radius: var(--tc-radius) !important;
  padding: 0 !important;
  border: 1px solid var(--tc-border) !important;
  background: #fff !important;
  box-shadow: var(--tc-shadow) !important;
  overflow: hidden !important;
  position: relative !important;

  display: flex !important;
  flex-direction: column !important;

  transition: all .35s cubic-bezier(0.4, 0, 0.2, 1);
  animation: cardFadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) backwards;
}

/* Staggered animation for multiple cards */
.tc-space-card.card-panel:nth-child(1) { animation-delay: 0.05s; }
.tc-space-card.card-panel:nth-child(2) { animation-delay: 0.1s; }
.tc-space-card.card-panel:nth-child(3) { animation-delay: 0.15s; }
.tc-space-card.card-panel:nth-child(4) { animation-delay: 0.2s; }
.tc-space-card.card-panel:nth-child(5) { animation-delay: 0.25s; }
.tc-space-card.card-panel:nth-child(6) { animation-delay: 0.3s; }

.tc-space-card.card-panel:hover{
  transform: translateY(-8px) scale(1.01);
  box-shadow: var(--tc-shadow-hover) !important;
  border-color: rgba(165,53,53,.15) !important;
}

/* ============================================================
   COVER - Enhanced with better overlay
============================================================ */
.tc-space-card .tc-cover{
  position: relative;
  height: var(--tc-cover-h);
  width: 100%;
  overflow: hidden;
  z-index:1;
  flex: 0 0 var(--tc-cover-h);
}

.tc-space-card .tc-cover__bg{
  position:absolute;
  inset:0;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  transform: scale(1.02);
  filter: saturate(1.1) contrast(1.05) brightness(.96);
  transition: all .5s cubic-bezier(0.4, 0, 0.2, 1);
}

.tc-space-card .tc-cover__shade{
  position:absolute;
  inset:0;
  background:
    linear-gradient(180deg,
      rgba(0,0,0,.02) 0%,
      rgba(0,0,0,.08) 40%,
      rgba(0,0,0,.25) 100%
    ),
    linear-gradient(135deg,
      rgba(165,53,53,.08) 0%,
      transparent 50%
    );
  pointer-events:none;
  transition: opacity .5s ease;
}

.tc-space-card:hover .tc-cover__shade{
  opacity: .85;
}

.tc-space-card .tc-top{
  position:absolute;
  left:16px;
  right:16px;
  bottom:14px;
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:12px;
  z-index: 2;
}

.tc-space-card .tc-avatar{
  display:flex;
  align-items:flex-end;
  gap:12px;
  transition: transform .35s cubic-bezier(0.4, 0, 0.2, 1);
}

.tc-space-card:hover .tc-avatar{
  transform: translateY(-3px);
}

.tc-space-card .tc-avatar .profile-user-photo,
.tc-space-card .tc-avatar img{
  border-radius: 14px !important;
  border: 3.5px solid rgba(255,255,255,.95) !important;
  box-shadow:
    0 4px 12px rgba(0,0,0,.15),
    0 12px 32px rgba(0,0,0,.2) !important;
  background:#fff !important;
  transition: all .35s cubic-bezier(0.4, 0, 0.2, 1);
}

.tc-space-card:hover .tc-avatar img{
  border-color: rgba(255,255,255,1) !important;
  box-shadow:
    0 6px 18px rgba(0,0,0,.2),
    0 16px 42px rgba(0,0,0,.25) !important;
}

.tc-space-card .tc-status{
  display:flex;
  align-items:center;
  gap:8px;
}

.tc-space-card .tc-icons{
  display:flex;
  align-items:center;
  justify-content:flex-end;
  gap:8px;
  opacity: .85;
  transition: opacity .3s ease;
}

.tc-space-card:hover .tc-icons{
  opacity: 1;
}

/* ============================================================
   BODY - Refined elegant spacing
============================================================ */
.tc-space-card .tc-body{
  position: relative;
  z-index:1;

  padding: 18px 18px 18px;

  background:
    linear-gradient(180deg,
      rgba(255,255,255,1) 0%,
      rgba(250,250,250,.98) 100%
    );
  border-top: 1px solid rgba(15,23,42,.06);
  border-bottom-left-radius: var(--tc-radius);
  border-bottom-right-radius: var(--tc-radius);

  flex: 1 1 auto !important;
  min-height: 0 !important;

  cursor: pointer;
  transition: background .3s ease;
}

.tc-space-card:hover .tc-body{
  background:
    linear-gradient(180deg,
      rgba(255,255,255,1) 0%,
      rgba(252,252,252,1) 100%
    );
}

.tc-space-card .tc-body-inner{
  display:flex;
  flex-direction: column;
  gap: 12px;
  min-height: 0;
  align-items: stretch;
}

/* TITLE */
.tc-space-card .tc-title{
  margin:0;
  color: var(--tc-title);
  font-weight: 800;
  letter-spacing: -0.02em;
  font-size: 17px;
  line-height: 1.3;

  height: 44.2px;
  min-height: 44.2px;

  display:-webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow:hidden;

  transition: color .3s ease;
}

.tc-space-card:hover .tc-title{
  color: var(--tc-red);
}

/* DESCRIPTION */
.tc-space-card .tc-desc{
  margin:0;
  color: var(--tc-muted);
  font-size: 13.5px;
  line-height: 1.6;
  font-weight: 500;

  height: 43.2px;
  min-height: 43.2px;

  display:-webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow:hidden;
}

/* METRICS */
.tc-metrics{
  display: flex;
  flex-direction: column;
  gap: 7px;
  padding: 12px 0;
  border-top: 1px solid rgba(15,23,42,.05);
  border-bottom: 1px solid rgba(15,23,42,.05);

  height: 92px;
  min-height: 92px;
}

.tc-metric-row{
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  line-height: 1.4;
  transition: transform .2s ease;
}

.tc-metric-row:hover{
  transform: translateX(3px);
}

.tc-metric-row svg{
  flex-shrink: 0;
  width: 17px;
  height: 17px;
  color: #A53535 !important;
  opacity: 1;
  transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1);
}

.tc-metric-row:hover svg{
  transform: scale(1.15);
}

.tc-metric-row .tc-metric-label{
  flex: 1;
  color: rgba(17,24,39,.90);
  font-weight: 600;
  font-size: 12.5px;
  letter-spacing: 0.01em;
}

.tc-metric-row .tc-metric-value{
  font-weight: 800;
  letter-spacing: -0.01em;
  font-size: 13.5px;
  color: rgba(17,24,39,.95);
}

/* STATUS */
.tc-status-badge-wrapper{
  display: flex;
  justify-content: center;
  width: 100%;
}

.tc-status-badge{
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 0;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  transition: all .25s cubic-bezier(0.4, 0, 0.2, 1);
  background: none;
  border: none;
  box-shadow: none;
}

.tc-status-badge:hover{
  opacity: 0.8;
}

.tc-status-badge.tc-status-active{ color: #059669; }
.tc-status-badge.tc-status-finished{ color: #dc2626; }

.tc-status-badge svg{
  width: 13px;
  height: 13px;
  transition: transform .3s ease;
}

.tc-status-badge:hover svg{
  transform: scale(1.1);
}

/* TAGS */
.tc-space-card .card-tags{
  margin-top: 0 !important;
  display:flex;
  flex-wrap:wrap;
  gap:7px;
}

.tc-space-card .card-tags .label,
.tc-space-card .card-tags .badge,
.tc-space-card .card-tags a{
  display:inline-flex !important;
  align-items:center !important;
  padding: 6px 12px !important;
  border-radius: 999px !important;
  border: 1px solid rgba(15,23,42,.08) !important;
  background: rgba(248,250,252,.8) !important;
  color: rgba(51,65,85,.8) !important;
  font-weight: 650 !important;
  font-size: 11px !important;
  letter-spacing: 0.01em !important;
  text-decoration:none !important;
  transition: all .25s cubic-bezier(0.4, 0, 0.2, 1) !important;
  backdrop-filter: blur(10px) !important;
}

.tc-space-card .card-tags .label:hover,
.tc-space-card .card-tags .badge:hover,
.tc-space-card .card-tags a:hover{
  background: rgba(255,255,255,1) !important;
  border-color: rgba(165,53,53,.2) !important;
  color: var(--tc-red) !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 4px 12px rgba(0,0,0,.08) !important;
}

.tc-space-card .tc-footer{ display: none !important; }

.tc-space-card.card-panel:hover .tc-cover__bg{
  transform: scale(1.08);
  filter: saturate(1.15) contrast(1.08) brightness(.92);
}

.tc-space-card a{
  text-decoration:none !important;
  color: inherit !important;
}

@media (max-width: 768px){
  .tc-space-card{ --tc-cover-h: 140px; --tc-radius: 16px; }
  .tc-space-card .tc-body{ padding: 14px 14px 14px; }
  .tc-metrics{ gap: 6px; padding: 10px 0; }
  .tc-metric-row{ font-size: 12px; }
  .tc-space-card .tc-title{ font-size: 15.5px; }
  .tc-space-card .tc-desc{ font-size: 12.5px; }
}

.tc-space-card .tc-body:focus-visible{
  outline: 2px solid var(--tc-red);
  outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
  .tc-space-card.card-panel,
  .tc-space-card .tc-cover__bg,
  .tc-space-card .tc-avatar,
  .tc-metric-row,
  .tc-status-badge{
    transition: none !important;
    animation: none !important;
  }
}
</style>

<div class="card-panel tc-space-card<?= $space->isArchived() ? ' card-archived' : '' ?>"
     data-space-id="<?= $space->id ?>"
     data-space-guid="<?= Html::encode($space->guid) ?>">

    <!-- COVER (clickable) -->
    <a href="<?= $spaceUrl ?>" class="card-space-link" aria-label="<?= Html::encode($space->name) ?>">
        <div class="tc-cover">
            <div class="tc-cover__bg"
                 style="background-image: <?= $hasCover ? 'url(\'' . Html::encode($coverUrl) . '\')' : Html::encode($fallbackCover) ?>;">
            </div>
            <div class="tc-cover__shade" aria-hidden="true"></div>

            <div class="tc-top">
                <div class="tc-avatar">
                    <?= Image::widget([
                        'space' => $space,
                        'width' => 64,
                    ]) ?>
                    <div class="tc-status">
                        <?= SpaceDirectoryStatus::widget(['space' => $space]) ?>
                    </div>
                </div>

                <div class="tc-icons">
                    <?= SpaceDirectoryIcons::widget(['space' => $space]) ?>
                </div>
            </div>
        </div>
    </a>

    <!-- BODY -->
    <div class="tc-body"
         role="link"
         tabindex="0"
         onclick="window.location.href='<?= Html::encode($spaceUrl) ?>'"
         onkeydown="if(event.key==='Enter' || event.key===' '){ event.preventDefault(); window.location.href='<?= Html::encode($spaceUrl) ?>'; }">

        <div class="tc-body-inner">
            <div class="tc-title"><?= Html::encode($space->name) ?></div>

            <?php if (trim((string)$space->description) !== '') : ?>
                <p class="tc-desc"><?= Html::encode($space->description) ?></p>
            <?php endif; ?>

            <div class="tc-metrics">
                <div class="tc-metric-row tc-metric-responses">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="tc-metric-value"><?= Html::encode(number_format($responseCount, 0, ',', ' ')) ?></span>
                    <span class="tc-metric-label">R&eacute;ponse(s)</span>
                </div>

                <div class="tc-metric-row tc-metric-appreciations">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="tc-metric-value"><?= Html::encode($appreciationCount) ?></span>
                    <span class="tc-metric-label">Appr&eacute;ciation(s)</span>
                </div>

                <div class="tc-metric-row tc-metric-participants">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="tc-metric-value"><?= Html::encode(number_format($participantCount, 0, ',', ' ')) ?></span>
                    <span class="tc-metric-label">Participant(s)</span>
                </div>
            </div>

            <div class="tc-status-badge-wrapper">
                <div class="tc-status-badge <?= Html::encode($statusClass) ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 6v6l4 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?= $statusText ?>
                </div>
            </div>

            <?= SpaceDirectoryTagList::widget([
                'space' => $space,
                'template' => '<div class="card-tags">{tags}</div>',
            ]) ?>
        </div>
    </div>

    <?= SpaceDirectoryActionButtons::widget([
        'space' => $space,
        'template' => '<div class="tc-footer">{buttons}</div>',
    ]) ?>

</div>