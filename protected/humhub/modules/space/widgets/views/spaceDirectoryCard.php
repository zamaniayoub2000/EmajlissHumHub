<?php
/**
 * ThemeCitoyen override - SpaceDirectoryCard
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

$hasCover = $space->getProfileBannerImage()->hasImage();
$coverUrl = $hasCover ? $space->getProfileBannerImage()->getUrl() : null;
$fallbackCover = 'linear-gradient(135deg, rgba(165,53,53,.18) 0%, rgba(6,56,59,.18) 55%, rgba(205,133,63,.14) 100%)';
$spaceUrl = $space->getUrl();
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap');

.tc-space-card {
  --tc-red:    #902727;
  --tc-dark:   #06383B;
  --tc-border: rgba(15,23,42,.08);
  --tc-shadow: 0 2px 8px rgba(0,0,0,.04), 0 12px 32px rgba(0,0,0,.06);
  --tc-shadow-hover: 0 8px 24px rgba(0,0,0,.08), 0 24px 56px rgba(0,0,0,.12);
  --tc-title:  rgba(17,24,39,.95);
  --tc-muted:  rgba(71,85,105,.85);
  --tc-radius: 20px;
  --tc-cover-h: 140px;
  font-family: 'Ubuntu', sans-serif !important;
}

@keyframes cardFadeIn {
  from { opacity:0; transform:translateY(20px) scale(0.98); }
  to   { opacity:1; transform:translateY(0) scale(1); }
}

/* Shimmer sweep across card on hover */
@keyframes shimmerSweep {
  0%   { transform: translateX(-100%) skewX(-12deg); opacity: 0; }
  40%  { opacity: 1; }
  100% { transform: translateX(220%) skewX(-12deg); opacity: 0; }
}

/* Soft glow pulse on border */
@keyframes borderGlow {
  0%, 100% { box-shadow: 0 0 0 0 rgba(165,53,53,0), var(--tc-shadow-hover); }
  50%       { box-shadow: 0 0 0 4px rgba(165,53,53,.08), var(--tc-shadow-hover); }
}

/* -- Outer anchor wraps the whole card -- */
.tc-space-card-link {
  display: block;
  text-decoration: none !important;
  color: inherit !important;
  border-radius: var(--tc-radius);
  outline-offset: 3px;
}

.tc-space-card-link:focus-visible {
  outline: 2px solid #A53535;
}

.tc-space-card.card-panel {
  border-radius: var(--tc-radius) !important;
  padding: 0 !important;
  border: 1px solid var(--tc-border) !important;
  background: #fff !important;
  box-shadow: var(--tc-shadow) !important;
  overflow: hidden !important;
  position: relative !important;
  display: flex !important;
  flex-direction: column !important;
  transition: all .35s cubic-bezier(0.4,0,0.2,1);
  animation: cardFadeIn 0.6s cubic-bezier(0.4,0,0.2,1) backwards;
  min-width: 0;
}

.tc-space-card.card-panel:nth-child(1){animation-delay:.05s}
.tc-space-card.card-panel:nth-child(2){animation-delay:.10s}
.tc-space-card.card-panel:nth-child(3){animation-delay:.15s}
.tc-space-card.card-panel:nth-child(4){animation-delay:.20s}
.tc-space-card.card-panel:nth-child(5){animation-delay:.25s}
.tc-space-card.card-panel:nth-child(6){animation-delay:.30s}

.tc-space-card-link:hover .tc-space-card.card-panel {
  transform: translateY(-6px) scale(1.008);
  box-shadow: var(--tc-shadow-hover) !important;
  border-color: rgba(165,53,53,.22) !important;
  animation: borderGlow 1.8s ease-in-out infinite !important;
}

/* Shimmer overlay — lives on the card itself */
.tc-space-card.card-panel::after {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 10;
  pointer-events: none;
  background: linear-gradient(
    105deg,
    transparent 40%,
    rgba(255,255,255,.18) 50%,
    transparent 60%
  );
  transform: translateX(-100%) skewX(-12deg);
  opacity: 0;
  transition: none;
  border-radius: var(--tc-radius);
}

.tc-space-card-link:hover .tc-space-card.card-panel::after {
  animation: shimmerSweep 0.7s cubic-bezier(0.4,0,0.2,1) forwards;
}

.tc-space-card .tc-cover {
  position: relative;
  height: var(--tc-cover-h);
  width: 100%;
  overflow: hidden;
  flex: 0 0 var(--tc-cover-h);
}

.tc-space-card .tc-cover__bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  transform: scale(1.02);
  filter: saturate(1.1) contrast(1.05) brightness(.96);
  transition: all .5s cubic-bezier(0.4,0,0.2,1);
}

.tc-space-card-link:hover .tc-cover__bg {
  transform: scale(1.08);
  filter: saturate(1.15) contrast(1.08) brightness(.92);
}

.tc-space-card .tc-cover__shade {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(180deg, rgba(0,0,0,.02) 0%, rgba(0,0,0,.08) 40%, rgba(0,0,0,.25) 100%),
    linear-gradient(135deg, rgba(165,53,53,.08) 0%, transparent 50%);
  pointer-events: none;
  transition: opacity .5s ease;
}

.tc-space-card-link:hover .tc-cover__shade { opacity: .85; }

.tc-space-card .tc-top {
  position: absolute;
  left: 18px; right: 18px; bottom: 16px;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 12px;
  z-index: 2;
}

.tc-space-card .tc-avatar {
  display: flex;
  align-items: flex-end;
  gap: 12px;
  transition: transform .35s cubic-bezier(0.4,0,0.2,1);
}

.tc-space-card-link:hover .tc-avatar { transform: translateY(-4px); }

.tc-space-card-link:hover .tc-avatar .profile-user-photo,
.tc-space-card-link:hover .tc-avatar img {
  box-shadow: 0 0 0 3px rgba(165,53,53,.18), 0 6px 18px rgba(0,0,0,.22), 0 18px 42px rgba(0,0,0,.26) !important;
}

.tc-space-card .tc-avatar .profile-user-photo,
.tc-space-card .tc-avatar img {
  width: 80px !important;
  height: 80px !important;
  border-radius: 16px !important;
  border: 4px solid rgba(255,255,255,.97) !important;
  box-shadow: 0 4px 14px rgba(0,0,0,.18), 0 14px 36px rgba(0,0,0,.22) !important;
  background: #fff !important;
  object-fit: cover !important;
  transition: all .35s cubic-bezier(0.4,0,0.2,1);
}

.tc-space-card .tc-icons {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
  opacity: .85;
  transition: opacity .3s ease;
}

.tc-space-card-link:hover .tc-icons { opacity: 1; }

.tc-space-card .tc-body {
  position: relative;
  z-index: 1;
  padding: 0;
  font-family: 'Ubuntu', sans-serif !important;
  background: #fff;
  border-top: 1px solid rgba(15,23,42,.06);
  border-bottom-left-radius: var(--tc-radius);
  border-bottom-right-radius: var(--tc-radius);
  flex: 1 1 auto !important;
  min-height: 130px;
  transition: background .3s ease;
}

.tc-space-card .tc-body-inner {
  display: flex;
  flex-direction: column;
  gap: 0;
}

/* Title */
.tc-space-card .tc-title {
  font-family: 'Ubuntu', sans-serif !important;
  font-size: 18px;
  font-weight: 700;
  color: var(--tc-title);
  letter-spacing: -0.01em;
  line-height: 1.3;
  margin: 0;
  padding: 0 20px;
  background: #fff;
  height: 80px;
  display: flex;
  align-items: center;
  overflow: hidden;
  transition: color .3s ease;
}

.tc-space-card-link:hover .tc-title { color: var(--tc-red); }

/* Description */
.tc-space-card .tc-desc-wrap {
  padding: 12px 20px 16px;
  background: #fff;
}

.tc-space-card .tc-desc {
  font-family: 'Ubuntu', sans-serif !important;
  font-size: 13.5px;
  font-weight: 400;
  color: var(--tc-muted);
  line-height: 1.65;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.tc-space-card .tc-separator {
  border: none;
  height: 2px;
  background: linear-gradient(90deg, transparent 0%, #555 30%, #999 70%, transparent 100%);
  margin: 0;
}

.tc-space-card .card-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
  padding: 0 20px 14px;
}

.tc-space-card .card-tags .label,
.tc-space-card .card-tags .badge,
.tc-space-card .card-tags a {
  font-family: 'Ubuntu', sans-serif !important;
  display: inline-flex !important;
  align-items: center !important;
  padding: 5px 12px !important;
  border-radius: 999px !important;
  border: 1px solid rgba(15,23,42,.08) !important;
  background: rgba(248,250,252,.8) !important;
  color: rgba(51,65,85,.8) !important;
  font-weight: 500 !important;
  font-size: 11px !important;
  text-decoration: none !important;
  transition: all .25s cubic-bezier(0.4,0,0.2,1) !important;
}

.tc-space-card .card-tags .label:hover,
.tc-space-card .card-tags .badge:hover,
.tc-space-card .card-tags a:hover {
  border-color: rgba(165,53,53,.2) !important;
  color: var(--tc-red) !important;
  background: rgba(165,53,53,.05) !important;
  transform: translateY(-2px) !important;
}

.tc-space-card .tc-footer { display: none !important; }

@media (max-width: 768px) {
  .tc-space-card { --tc-cover-h: 150px; --tc-radius: 16px; }
  .tc-space-card .tc-title { font-size: 15.5px; padding: 12px 16px; }
  .tc-space-card .tc-desc-wrap { padding: 10px 16px 14px; }
  .tc-space-card .tc-desc { font-size: 12.5px; }
  .tc-space-card .tc-avatar .profile-user-photo,
  .tc-space-card .tc-avatar img { width: 68px !important; height: 68px !important; }
}

@media (prefers-reduced-motion: reduce) {
  .tc-space-card.card-panel,
  .tc-space-card .tc-cover__bg,
  .tc-space-card .tc-avatar { transition: none !important; animation: none !important; }
  .tc-space-card.card-panel::after { animation: none !important; }
  .tc-space-card .tc-title::after { transition: none !important; }
}
</style>

<!-- Single <a> wraps the entire card -->
<a href="<?= Html::encode($spaceUrl) ?>"
   class="tc-space-card-link"
   aria-label="<?= Html::encode($space->name) ?>">

    <div class="card-panel tc-space-card<?= $space->isArchived() ? ' card-archived' : '' ?>"
         data-space-id="<?= $space->id ?>"
         data-space-guid="<?= Html::encode($space->guid) ?>">

        <div class="tc-cover">
            <div class="tc-cover__bg"
                 style="background-image: <?= $hasCover ? 'url(\'' . Html::encode($coverUrl) . '\')' : Html::encode($fallbackCover) ?>;"></div>
            <div class="tc-cover__shade" aria-hidden="true"></div>
            <div class="tc-top">
                <div class="tc-avatar">
                    <?= Image::widget(['space' => $space, 'width' => 64]) ?>
                    <div class="tc-status">
                        <?= SpaceDirectoryStatus::widget(['space' => $space]) ?>
                    </div>
                </div>
                <div class="tc-icons">
                    <?= SpaceDirectoryIcons::widget(['space' => $space]) ?>
                </div>
            </div>
        </div>

        <div class="tc-body">
            <div class="tc-body-inner">
                <div class="tc-title"><?= Html::encode($space->name) ?></div>
                <?php if (trim((string)$space->description) !== ''): ?>
                    <hr class="tc-separator">
                    <div class="tc-desc-wrap">
                        <p class="tc-desc"><?= Html::encode($space->description) ?></p>
                    </div>
                <?php endif; ?>
                <?= SpaceDirectoryTagList::widget([
                    'space'    => $space,
                    'template' => '<div class="card-tags">{tags}</div>',
                ]) ?>
            </div>
        </div>

        <?= SpaceDirectoryActionButtons::widget([
            'space'    => $space,
            'template' => '<div class="tc-footer">{buttons}</div>',
        ]) ?>

    </div>

</a>