<?php
/**
 * CSEFRS ThemeCitoyen — SpaceDirectoryCard (Complete Redesign)
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

try {
    Yii::$app->response->headers->set('Content-Type', 'text/html; charset=UTF-8');
} catch (\Throwable $e) {}

$hasCover  = $space->getProfileBannerImage()->hasImage();
$coverUrl  = $hasCover ? $space->getProfileBannerImage()->getUrl() : null;
$spaceUrl  = $space->getUrl();

// Curated fallback gradients — rotate by space ID for variety
$gradients = [
    'linear-gradient(135deg, #06383B 0%, #0b5c62 60%, #0e7a82 100%)',
    'linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%)',
    'linear-gradient(135deg, #2d1b33 0%, #4a2c5a 50%, #6b3d82 100%)',
    'linear-gradient(135deg, #1b2838 0%, #2a3f55 50%, #1e5799 100%)',
    'linear-gradient(135deg, #1c2b1c 0%, #2d4a2d 50%, #3d6b3d 100%)',
];
$fallbackGrad = $gradients[$space->id % count($gradients)];

$isActive         = true;
$responseCount    = 0;
$participantCount = 0;
$appreciationCount = 0;

$statusLabel = $isActive ? 'Ouverte' : 'Terminée';
$statusColor = $isActive ? '#059669' : '#dc2626';
$statusBg    = $isActive ? 'rgba(5,150,105,.1)' : 'rgba(220,38,38,.1)';
?>

<meta charset="utf-8">

<style>
/* ================================================================
   CSEFRS Space Card — refined, institutional, form-portal style
================================================================ */
.csf-card {
  --csf-teal:    #06383B;
  --csf-red:     #A53535;
  --csf-gold:    #B8860B;
  --csf-ink:     #141414;
  --csf-muted:   rgba(20,20,20,.62);
  --csf-border:  rgba(20,20,20,.08);
  --csf-ivory:   #F8F6F2;

  font-family: 'DM Sans', 'Helvetica Neue', Arial, sans-serif;
  background: #fff;
  border-radius: 12px;
  border: 1px solid var(--csf-border);
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,.04), 0 8px 28px rgba(0,0,0,.06);
  display: flex;
  flex-direction: column;
  position: relative;
  transition: transform .32s cubic-bezier(.22,1,.36,1),
              box-shadow .32s cubic-bezier(.22,1,.36,1),
              border-color .3s ease;
  animation: csfCardIn .55s cubic-bezier(.22,1,.36,1) backwards;
  cursor: pointer;
  text-decoration: none !important;
  color: inherit !important;
  height: 100%;
}

@keyframes csfCardIn {
  from { opacity:0; transform:translateY(22px) scale(.98); }
  to   { opacity:1; transform:none; }
}

/* stagger */
.csf-card:nth-child(1){animation-delay:.05s}
.csf-card:nth-child(2){animation-delay:.10s}
.csf-card:nth-child(3){animation-delay:.15s}
.csf-card:nth-child(4){animation-delay:.20s}
.csf-card:nth-child(5){animation-delay:.25s}
.csf-card:nth-child(6){animation-delay:.30s}
.csf-card:nth-child(7){animation-delay:.35s}
.csf-card:nth-child(8){animation-delay:.40s}
.csf-card:nth-child(9){animation-delay:.45s}

.csf-card:hover {
  transform: translateY(-8px) scale(1.008);
  box-shadow: 0 16px 48px rgba(0,0,0,.12), 0 4px 12px rgba(0,0,0,.06);
  border-color: rgba(165,53,53,.22);
}

/* ── COVER ──────────────────────────────────────────── */
.csf-cover {
  position: relative;
  height: 180px;
  flex-shrink: 0;
  overflow: hidden;
}

.csf-cover__bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  transform: scale(1.04);
  transition: transform .55s cubic-bezier(.22,1,.36,1),
              filter .55s ease;
  filter: brightness(.95) saturate(1.05);
}

.csf-card:hover .csf-cover__bg {
  transform: scale(1.1);
  filter: brightness(.88) saturate(1.12);
}

/* gradient overlay — teal tint bottom fade */
.csf-cover__overlay {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(180deg, rgba(6,56,59,.0) 20%, rgba(6,56,59,.55) 100%),
    linear-gradient(135deg, rgba(165,53,53,.06) 0%, transparent 60%);
  pointer-events: none;
  transition: opacity .4s ease;
}

.csf-card:hover .csf-cover__overlay {
  opacity: .85;
}

/* status badge — top right */
.csf-status-badge {
  position: absolute;
  top: 14px; right: 14px;
  z-index: 3;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  border-radius: 999px;
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,.18);
  transition: transform .25s ease;
}

.csf-card:hover .csf-status-badge { transform: translateY(-2px); }

.csf-status-badge__dot {
  width: 6px; height: 6px;
  border-radius: 50%;
}

/* category label — bottom left of cover */
.csf-cover__category {
  position: absolute;
  bottom: 14px; left: 14px;
  z-index: 3;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .14em;
  text-transform: uppercase;
  color: rgba(255,255,255,.75);
  display: flex;
  align-items: center;
  gap: 6px;
}

.csf-cover__category::before {
  content: '';
  width: 16px; height: 1.5px;
  background: rgba(255,255,255,.5);
  border-radius: 999px;
}

/* ── BODY ───────────────────────────────────────────── */
.csf-body {
  padding: 20px 20px 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
  background: #fff;
  transition: background .3s ease;
}

.csf-card:hover .csf-body {
  background: #fdfcfb;
}

/* push metrics to bottom */
.csf-body-top {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.csf-metrics-bottom {
  margin-top: auto;
  border-top: 1px solid var(--csf-border);
  padding-bottom: 14px;
}

/* Title */
.csf-title {
  font-family: 'Playfair Display', serif;
  font-size: 18px;
  font-weight: 700;
  color: var(--csf-ink);
  line-height: 1.28;
  letter-spacing: -.1px;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  transition: color .2s ease;
}

.csf-card:hover .csf-title { color: var(--csf-teal); }

/* Description */
.csf-desc {
  font-size: 13.5px;
  color: var(--csf-muted);
  line-height: 1.65;
  font-weight: 400;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* ── DIVIDER ─────────────────────────────────────────── */
.csf-divider {
  height: 1px;
  background: var(--csf-border);
  margin: 4px 0;
}

/* ── META ROW ────────────────────────────────────────── */
.csf-meta {
  display: flex;
  align-items: center;
  gap: 0;
  flex-wrap: nowrap;
  justify-content: space-between;
  padding: 10px 0 0;
  margin-top: auto;
}

.csf-meta-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  font-size: 11px;
  color: var(--csf-muted);
  font-weight: 500;
  text-align: center;
  flex: 1;
  padding: 8px 6px;
  border-radius: 8px;
  transition: background .2s ease, color .2s ease;
}

.csf-meta-item:hover {
  background: rgba(165,53,53,.05);
  color: var(--csf-red);
}

.csf-meta-item svg {
  width: 16px; height: 16px;
  color: var(--csf-red);
  flex-shrink: 0;
  transition: transform .25s ease;
}

.csf-meta-item:hover svg { transform: scale(1.15); }

.csf-meta-item span {
  font-size: 11px;
  font-weight: 600;
  line-height: 1.2;
  white-space: nowrap;
}

.csf-meta-sep {
  width: 1px;
  height: 28px;
  background: var(--csf-border);
  flex-shrink: 0;
}

/* ── TAGS ─────────────────────────────────────────────── */
.csf-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 2px;
}

.csf-tags .label,
.csf-tags .badge,
.csf-tags a {
  display: inline-flex !important;
  align-items: center !important;
  padding: 4px 10px !important;
  border-radius: 999px !important;
  border: 1px solid var(--csf-border) !important;
  background: var(--csf-ivory) !important;
  color: var(--csf-muted) !important;
  font-size: 10.5px !important;
  font-weight: 600 !important;
  letter-spacing: .04em !important;
  text-decoration: none !important;
  transition: all .2s ease !important;
}

.csf-tags .label:hover,
.csf-tags .badge:hover,
.csf-tags a:hover {
  border-color: rgba(165,53,53,.25) !important;
  color: var(--csf-red) !important;
  background: rgba(165,53,53,.05) !important;
  transform: translateY(-1px) !important;
}

/* ── FOOTER CTA ───────────────────────────────────────── */
.csf-footer {
  padding: 14px 22px 18px;
  border-top: 1px solid var(--csf-border);
  background: var(--csf-ivory);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  transition: background .3s ease;
}

.csf-card:hover .csf-footer {
  background: #f0ede8;
}

.csf-footer__cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  font-weight: 700;
  color: var(--csf-red);
  letter-spacing: .03em;
  text-decoration: none !important;
  transition: gap .25s ease, color .2s ease;
}

.csf-footer__cta svg {
  width: 14px; height: 14px;
  transition: transform .25s cubic-bezier(.22,1,.36,1);
}

.csf-card:hover .csf-footer__cta { gap: 12px; color: var(--csf-teal); }
.csf-card:hover .csf-footer__cta svg { transform: translateX(4px); }

.csf-footer__form-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .14em;
  text-transform: uppercase;
  color: var(--csf-muted);
  display: flex;
  align-items: center;
  gap: 5px;
}

.csf-footer__form-label svg {
  width: 12px; height: 12px;
  color: var(--csf-gold);
}

/* ── HIDE HUMHUB DEFAULTS ─────────────────────────────── */
.csf-hh-hidden { display: none !important; }

/* ── ACCESSIBILITY ───────────────────────────────────── */
.csf-card:focus-visible {
  outline: 2.5px solid var(--csf-red);
  outline-offset: 3px;
}

@media (prefers-reduced-motion: reduce) {
  .csf-card, .csf-cover__bg, .csf-status-badge { transition: none !important; animation: none !important; }
}
</style>

<div class="card-panel csf-card<?= $space->isArchived() ? ' card-archived' : '' ?>"
     role="link"
     tabindex="0"
     data-space-id="<?= $space->id ?>"
     onclick="window.location.href='<?= Html::encode($spaceUrl) ?>'"
     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.location.href='<?= Html::encode($spaceUrl) ?>';}">

  <!-- ── COVER ── -->
  <a href="<?= Html::encode($spaceUrl) ?>" tabindex="-1" aria-label="<?= Html::encode($space->name) ?>" style="text-decoration:none;">
    <div class="csf-cover">

      <!-- background image or gradient -->
      <div class="csf-cover__bg"
           style="background: <?= $hasCover ? 'url(\'' . Html::encode($coverUrl) . '\') center/cover no-repeat' : $fallbackGrad ?>;">
      </div>
      <div class="csf-cover__overlay" aria-hidden="true"></div>

      <!-- status pill -->
      <div class="csf-status-badge"
           style="background:<?= $statusBg ?>;color:<?= $statusColor ?>;">
        <span class="csf-status-badge__dot" style="background:<?= $statusColor ?>;"></span>
        <?= $statusLabel ?>
      </div>

      <!-- category hint -->
      <div class="csf-cover__category">Formulaire de consultation</div>

    </div>
  </a>

  <!-- ── BODY ── -->
  <div class="csf-body">

    <div class="csf-body-top">
      <div class="csf-title"><?= Html::encode($space->name) ?></div>

      <?php if (trim((string)$space->description) !== ''): ?>
        <p class="csf-desc"><?= Html::encode($space->description) ?></p>
      <?php endif; ?>

      <?= SpaceDirectoryTagList::widget([
        'space'    => $space,
        'template' => '<div class="csf-tags">{tags}</div>',
      ]) ?>
    </div>

    <!-- metrics — 3 columns, glued to bottom -->
    <div class="csf-metrics-bottom">
      <div class="csf-meta">

        <div class="csf-meta-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
          <span><?= Html::encode(number_format($responseCount, 0, ',', ' ')) ?> réponse<?= $responseCount !== 1 ? 's' : '' ?></span>
        </div>

        <div class="csf-meta-sep"></div>

        <div class="csf-meta-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          <span><?= Html::encode(number_format($participantCount, 0, ',', ' ')) ?> participant<?= $participantCount !== 1 ? 's' : '' ?></span>
        </div>

        <div class="csf-meta-sep"></div>

        <div class="csf-meta-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3z"/>
            <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
          </svg>
          <span><?= Html::encode(number_format($appreciationCount, 0, ',', ' ')) ?> appréciation<?= $appreciationCount !== 1 ? 's' : '' ?></span>
        </div>

      </div>
    </div>

  </div>

  <!-- ── FOOTER CTA ── -->
  <div class="csf-footer">
    <div class="csf-footer__form-label">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="16" y1="13" x2="8" y2="13"/>
        <line x1="16" y1="17" x2="8" y2="17"/>
        <polyline points="10 9 9 9 8 9"/>
      </svg>
      Formulaire officiel
    </div>
    <a href="<?= Html::encode($spaceUrl) ?>" class="csf-footer__cta" tabindex="-1">
      Accéder
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 12h14M12 5l7 7-7 7"/>
      </svg>
    </a>
  </div>

  <!-- hide humhub action buttons -->
  <?= SpaceDirectoryActionButtons::widget([
    'space'    => $space,
    'template' => '<div class="csf-hh-hidden">{buttons}</div>',
  ]) ?>
  <div class="csf-hh-hidden">
    <?= SpaceDirectoryIcons::widget(['space' => $space]) ?>
    <?= SpaceDirectoryStatus::widget(['space' => $space]) ?>
    <?= Image::widget(['space' => $space, 'width' => 64]) ?>
  </div>

</div>