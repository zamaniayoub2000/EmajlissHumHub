<?php
/**
 * ThemeCitoyen override - Spaces directory page (/spaces)
 * ENHANCED v4.1 — Filters removed, UI polished
 * Encoding: UTF-8 SAFE
 */

header('Content-Type: text/html; charset=UTF-8');

use humhub\assets\CardsAsset;
use humhub\helpers\Html;
use humhub\modules\space\components\SpaceDirectoryQuery;
use humhub\modules\space\widgets\SpaceDirectoryCard;
use yii\web\View;

/* @var $this View */
/* @var $spaces SpaceDirectoryQuery */

CardsAsset::register($this);

$this->registerJs("document.body.classList.add('tc-spaces-page');", View::POS_END);

$bannerUrl = Yii::$app->request->baseUrl . '/themes/theme2/img/banner4.png';

$totalCount = null;
try {
    $totalCount = $spaces->pagination->totalCount ?? null;
} catch (\Throwable $e) {
    $totalCount = null;
}

$this->registerJs(<<<'JS'
(function(){

  /* -- SMOOTH SCROLL -- */
  document.querySelectorAll('a[href^="#"]').forEach(function(a){
    a.addEventListener('click', function(e){
      var id = this.getAttribute('href');
      var target = document.querySelector(id);
      if(!target) return;
      e.preventDefault();
      var offset = 72;
      var y = target.getBoundingClientRect().top + (window.scrollY || window.pageYOffset) - offset;
      window.scrollTo({ top: y, behavior: 'smooth' });
    });
  });

  /* -- PARALLAX -- */
  var hero    = document.querySelector('.race-hero');
  var heroImg = document.querySelector('.race-hero__img');
  if(hero && heroImg){
    var ticking = false;
    function updateParallax(){
      ticking = false;
      var y = window.scrollY || window.pageYOffset || 0;
      var imgShift     = Math.min(180, Math.max(0, y * 0.38));
      var contentShift = Math.min(80,  Math.max(0, y * 0.08));
      hero.style.setProperty('--hero-img-shift',     imgShift.toFixed(1)     + 'px');
      hero.style.setProperty('--hero-content-shift', contentShift.toFixed(1) + 'px');
    }
    window.addEventListener('scroll', function(){
      if(ticking) return; ticking = true;
      window.requestAnimationFrame(updateParallax);
    }, {passive:true});
    updateParallax();
  }

  /* -- SCROLL-REVEAL -- */
  var revealEls = document.querySelectorAll('.race-reveal');
  if('IntersectionObserver' in window){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){
          entry.target.classList.add('race-revealed');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach(function(el){ io.observe(el); });
  } else {
    revealEls.forEach(function(el){ el.classList.add('race-revealed'); });
  }

  /* -- MAGNETIC BUTTONS -- */
  document.querySelectorAll('.race-btn--magnetic').forEach(function(btn){
    btn.addEventListener('mousemove', function(e){
      var r  = btn.getBoundingClientRect();
      var dx = (e.clientX - r.left  - r.width  / 2) * 0.26;
      var dy = (e.clientY - r.top   - r.height / 2) * 0.26;
      btn.style.transform = 'translate(' + dx + 'px,' + dy + 'px) scale(1.04)';
    });
    btn.addEventListener('mouseleave', function(){
      btn.style.transform = '';
    });
  });

  /* -- ANIMATED COUNTERS -- */
  function animateCounter(el){
    var raw   = el.getAttribute('data-target') || '';
    var isK   = raw.indexOf('K') !== -1;
    var isPct = raw.indexOf('%') !== -1;
    var end   = parseFloat(raw.replace(/[^0-9.]/g, ''));
    if(isNaN(end)) return;
    var start = 0, duration = 1800, startTime = null;
    function step(t){
      if(!startTime) startTime = t;
      var prog = Math.min((t - startTime) / duration, 1);
      var ease = 1 - Math.pow(1 - prog, 3);
      var val  = start + (end - start) * ease;
      if(isK)        el.textContent = val.toFixed(1).replace('.', ',') + 'K';
      else if(isPct) el.textContent = Math.round(val) + '%';
      else {
        var n = Math.round(val);
        el.textContent = n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '\u00A0');
      }
      if(prog < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  var statIO = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if(entry.isIntersecting){
        animateCounter(entry.target);
        statIO.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.race-quick-stat__value[data-target], .race-banner-stat__value[data-target]').forEach(function(el){
    statIO.observe(el);
  });

})();
JS, View::POS_END);
?>

<meta charset="UTF-8">

<style>
/* ============================================================
   SCROLL / OVERFLOW FIX
============================================================ */
body.tc-spaces-page,
body.tc-spaces-page html { height: auto !important; }
body.tc-spaces-page { overflow-y: auto !important; }
body.tc-spaces-page #layout-content,
body.tc-spaces-page .layout,
body.tc-spaces-page .layout-content-container,
body.tc-spaces-page .container.container-cards.container-spaces,
body.tc-spaces-page .container-cards.container-spaces {
  height: auto !important;
  min-height: 0 !important;
  max-height: none !important;
  overflow: visible !important;
}

/* ============================================================
   KEYFRAMES
============================================================ */
@keyframes slideInDown { from { opacity:0; transform:translateY(-22px); } to { opacity:1; transform:none; } }
@keyframes slideInUp   { from { opacity:0; transform:translateY(22px);  } to { opacity:1; transform:none; } }
@keyframes fadeInUp    { from { opacity:0; transform:translateY(14px);  } to { opacity:1; transform:none; } }
@keyframes scaleIn     { from { opacity:0; transform:scale(0.96);       } to { opacity:1; transform:scale(1); } }
@keyframes shimmer     { 0%{ background-position:-200% center; } 100%{ background-position:200% center; } }

/* ============================================================
   SCROLL REVEAL
============================================================ */
.race-reveal {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity .65s cubic-bezier(.22,1,.36,1),
              transform .65s cubic-bezier(.22,1,.36,1);
}
.race-reveal.race-revealed { opacity:1; transform:none; }
.race-reveal.rd1 { transition-delay:.07s; }
.race-reveal.rd2 { transition-delay:.14s; }
.race-reveal.rd3 { transition-delay:.21s; }
.race-reveal.rd4 { transition-delay:.28s; }

/* ============================================================
   PAGE TOKENS
============================================================ */
.race-spaces-page {
  --race-red:    #A53535;
  --race-red2:   #7A1F1F;
  --race-dark:   #06383B;
  --race-orange: #CD853F;

  --text:   rgba(17,24,39,.92);
  --muted:  rgba(17,24,39,.58);
  --border: rgba(15,23,42,.09);

  --radius-xl: 24px;
  --radius-lg: 18px;
  --radius-md: 14px;

  --hero-pull:       60px;
  --hero-bottom-gap: 20px;
  --sheet-overlap:   80px;

  --hero-img-shift:     0px;
  --hero-content-shift: 0px;

  position: relative;
}

/* ============================================================
   HERO
============================================================ */
.race-spaces-page .race-hero {
  width: 100vw;
  position: relative;
  left: 50%; right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  margin-top: 0 !important;
  transform: translateY(calc(-1 * var(--hero-pull))) !important;
  margin-bottom: var(--hero-bottom-gap) !important;
  overflow: hidden;
  height: 840px;
  animation: slideInDown .85s cubic-bezier(.22,1,.36,1);
}

.race-spaces-page .race-hero__img {
  position: absolute;
  left: 0; right: 0;
  top: -100px;
  width: 100%; height: calc(100% + 200px);
  object-fit: cover;
  object-position: center;
  display: block;
  margin: 0 !important; padding: 0 !important;
  transform: translate3d(0, var(--hero-img-shift), 0);
  will-change: transform;
  filter: saturate(1.1) brightness(.88);
  transition: filter .4s ease;
}

.race-spaces-page .race-hero__overlay {
  position: absolute; inset: 0;
  background:
    linear-gradient(105deg,
      rgba(10,16,26,.85) 0%,
      rgba(10,16,26,.52) 42%,
      rgba(10,16,26,.10) 72%
    ),
    linear-gradient(180deg,
      transparent 48%,
      rgba(10,16,26,.82) 100%
    );
  pointer-events: none;
}

.race-spaces-page .race-hero__cut {
  position: absolute;
  bottom: -2px; left: 0; right: 0;
  height: 140px;
  background: #fff;
  clip-path: polygon(0 55%, 100% 0%, 100% 100%, 0 100%);
  pointer-events: none;
}

.race-spaces-page .race-hero__content {
  position: absolute; inset: 0;
  pointer-events: none;
  transform: translate3d(0, var(--hero-content-shift), 0);
  will-change: transform;
}

.race-spaces-page .race-hero__content .container {
  height: 100%;
  display: flex;
  align-items: center;
}

.race-spaces-page .race-hero__copy {
  pointer-events: auto;
  max-width: 820px;
  padding-bottom: 80px;
}

.race-spaces-page .race-hero__title {
  margin: 0 0 22px 0;
  font-weight: 900;
  font-size: clamp(40px, 5.8vw, 72px);
  line-height: 1.08;
  letter-spacing: -1px;
  color: #fff;
  animation: slideInUp .85s cubic-bezier(.22,1,.36,1) .2s both;
  position: relative;
}

.race-spaces-page .race-hero__title em {
  font-style: normal;
  background: linear-gradient(90deg, #f9a825 0%, #ef5350 100%);
  background-size: 200% auto;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: shimmer 4s linear 1s infinite;
}

.race-spaces-page .race-hero__title::after {
  content: '';
  position: absolute;
  bottom: -12px; left: 0;
  width: 100px; height: 5px;
  background: linear-gradient(90deg, var(--race-red), transparent);
  border-radius: 999px;
}

.race-spaces-page .race-hero__subtitle {
  margin: 0 0 34px 0;
  color: rgba(255,255,255,.65);
  font-size: clamp(15px, 1.7vw, 18px);
  line-height: 1.72;
  font-weight: 400;
  max-width: 580px;
  animation: slideInUp .85s cubic-bezier(.22,1,.36,1) .3s both;
}

.race-spaces-page .race-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
  animation: slideInUp .85s cubic-bezier(.22,1,.36,1) .4s both;
}

/* ============================================================
   BUTTONS
============================================================ */
.race-spaces-page .race-btn {
  pointer-events: auto;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  height: 50px;
  padding: 0 26px;
  border-radius: var(--radius-md);
  font-weight: 700;
  font-size: 14px;
  text-decoration: none !important;
  cursor: pointer;
  border: 1px solid transparent;
  position: relative;
  overflow: hidden;
  transition: transform .2s cubic-bezier(.22,1,.36,1),
              box-shadow .2s ease,
              background .2s ease,
              border-color .2s ease;
}
.race-spaces-page .race-btn::after {
  content: '';
  position: absolute; inset: 0;
  background: rgba(255,255,255,0);
  transition: background .2s ease;
}
.race-spaces-page .race-btn:hover::after { background: rgba(255,255,255,.08); }

.race-spaces-page .race-btn--primary {
  background: linear-gradient(135deg, var(--race-red) 0%, var(--race-red2) 100%);
  color: #fff;
  box-shadow: 0 8px 28px rgba(165,53,53,.5);
}
.race-spaces-page .race-btn--primary:hover {
  box-shadow: 0 14px 44px rgba(165,53,53,.6);
}
.race-spaces-page .race-btn--magnetic {
  transition: transform .22s cubic-bezier(.22,1,.36,1),
              box-shadow .22s ease,
              background .2s ease,
              border-color .2s ease !important;
}

/* ============================================================
   BANNER STATS  (inside the red card, below title)
============================================================ */
.race-banner-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0;
  margin-top: 20px;
  border-top: 1px solid rgba(255,255,255,.15);
  padding-top: 18px;
}
.race-banner-stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 0 12px;
  border-right: 1px solid rgba(255,255,255,.15);
  cursor: default;
}
.race-banner-stat:last-child { border-right: none; }
.race-banner-stat__value {
  font-size: 22px;
  font-weight: 900;
  color: #fff;
  line-height: 1;
  font-variant-numeric: tabular-nums;
  letter-spacing: -.5px;
  text-shadow: 0 2px 8px rgba(0,0,0,.2);
}
.race-banner-stat__label {
  font-size: 9px;
  font-weight: 700;
  color: rgba(255,255,255,.55);
  text-transform: uppercase;
  letter-spacing: 1.6px;
}

/* ============================================================
   STATS BAR (keep class stub to avoid breaking responsive rules)
============================================================ */
.race-stats-bar-wrap {
  position: absolute;
  bottom: 148px;
  left: 50%;
  transform: translateX(-50%);
  width: calc(100% - 60px);
  max-width: 1140px;
  z-index: 10;
}

.race-quick-stats {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0;
  border-radius: 12px;
  overflow: hidden;
  background: rgba(10,16,26,.60);
  backdrop-filter: blur(20px) saturate(150%);
  border: 1px solid rgba(255,255,255,.11);
  /* subtle inner glow */
  box-shadow: inset 0 1px 0 rgba(255,255,255,.06), 0 20px 60px rgba(0,0,0,.3);
}

.race-quick-stat {
  padding: 16px 22px;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 12px;
  cursor: default;
  position: relative;
  border-right: 1px solid rgba(255,255,255,.08);
  transition: background .2s ease;
}
.race-quick-stat:last-child { border-right: none; }
.race-quick-stat:hover { background: rgba(255,255,255,.06); }

/* thin red accent line at top on hover */
.race-quick-stat::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 2px;
  background: linear-gradient(90deg, var(--race-red), transparent);
  opacity: 0;
  transition: opacity .25s ease;
}
.race-quick-stat:hover::before { opacity: 1; }

.race-quick-stat__icon {
  width: 26px; height: 26px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  opacity: .38;
  transition: opacity .2s ease;
}
.race-quick-stat:hover .race-quick-stat__icon { opacity: .65; }
.race-quick-stat__icon svg { color: #fff; width: 18px; height: 18px; stroke-width: 1.5; }

.race-quick-stat__content { min-width: 0; }

.race-quick-stat__value {
  font-size: 20px;
  font-weight: 800;
  color: #fff;
  line-height: 1;
  margin-bottom: 3px;
  font-variant-numeric: tabular-nums;
  letter-spacing: -.3px;
}

.race-quick-stat__label {
  font-size: 9px;
  font-weight: 700;
  color: rgba(255,255,255,.40);
  text-transform: uppercase;
  letter-spacing: 1.6px;
}

/* ============================================================
   SHEET
============================================================ */
.race-spaces-page .race-sheet {
  position: relative;
  z-index: 5;
  margin-top: calc(-1 * var(--sheet-overlap));
  padding-top: calc(var(--sheet-overlap) - 20px);
  border-top-left-radius: 30px;
  border-top-right-radius: 30px;
  background: #fff;
  animation: scaleIn .7s cubic-bezier(.22,1,.36,1) .3s both;
}

/* ============================================================
   SECTION BANNER — refined version
============================================================ */
.race-section-banner {
  padding: 0 0 32px;
}

.race-section-banner__card {
  background: linear-gradient(135deg, var(--race-red) 0%, var(--race-red2) 100%);
  border-radius: 16px;
  padding: 26px 40px;
  text-align: center;
  box-shadow: 0 8px 32px rgba(165,53,53,.30), inset 0 1px 0 rgba(255,255,255,.10);
  position: relative;
  overflow: hidden;
}

/* subtle diagonal grain texture overlay */
.race-section-banner__card::before {
  content: '';
  position: absolute; inset: 0;
  background: repeating-linear-gradient(
    -45deg,
    rgba(255,255,255,.02) 0px,
    rgba(255,255,255,.02) 1px,
    transparent 1px,
    transparent 6px
  );
  pointer-events: none;
}

/* glowing circle in the right side */
.race-section-banner__card::after {
  content: '';
  position: absolute;
  right: -60px; top: -60px;
  width: 220px; height: 220px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255,255,255,.07) 0%, transparent 70%);
  pointer-events: none;
}

.race-section-banner__title {
  font-size: clamp(20px, 2.6vw, 30px);
  font-weight: 900;
  color: #fff;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin: 0;
  line-height: 1;
  position: relative; /* above pseudo elements */
  text-shadow: 0 2px 12px rgba(0,0,0,.15);
}

/* ============================================================
   CARDS
============================================================ */
body.tc-spaces-page .race-spaces-page { padding-bottom: 120px !important; }
body.tc-spaces-page .row.cards {
  margin-bottom: 100px !important;
  animation: fadeInUp .8s cubic-bezier(.22,1,.36,1) .5s both;
}

/* ============================================================
   EMPTY STATE
============================================================ */
.race-empty-state {
  text-align: center;
  padding: 56px 24px;
  border-radius: var(--radius-xl);
  border: 1px dashed rgba(15,23,42,.10);
  background: rgba(249,250,251,.7);
}
.race-empty-state svg { margin-bottom: 14px; opacity: .25; }
.race-empty-state strong {
  display: block; font-size: 17px; font-weight: 800;
  color: var(--text); margin-bottom: 6px;
}
.race-empty-state p { color: var(--muted); margin: 0; font-size: 14px; }

/* ============================================================
   RESPONSIVE
============================================================ */
@media (max-width: 1200px) {
  .race-spaces-page .race-hero { height: 740px; }
  .race-quick-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .race-stats-bar-wrap { bottom: 110px; }
}

@media (max-width: 992px) {
  .race-spaces-page .race-hero { height: 640px; }
  .race-spaces-page { --sheet-overlap: 60px; }
  .race-spaces-page .race-hero__img { top: -70px; height: calc(100% + 140px); }
  .race-quick-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .race-stats-bar-wrap { width: calc(100% - 30px); bottom: 90px; }
}

@media (max-width: 768px) {
  .race-spaces-page .race-hero {
    height: auto;
    min-height: 560px;
    padding-bottom: 140px;
  }
  .race-spaces-page .race-hero__copy { padding-bottom: 20px; }
  .race-stats-bar-wrap {
    position: relative;
    bottom: auto; left: auto; transform: none;
    width: calc(100% - 32px);
    margin: 0 16px;
  }
  .race-hero__stats-anchor {
    position: absolute;
    bottom: 16px; left: 0; right: 0;
    padding: 0 16px;
  }
  .race-quick-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .race-quick-stat { padding: 11px 12px; }
  .race-quick-stat__value { font-size: 16px; }
  .race-quick-stat__label { font-size: 8px; }
}

@media (max-width: 576px) {
  .race-spaces-page .race-hero { min-height: 520px; padding-bottom: 120px; }
  .race-spaces-page .race-hero__img { top: -60px; height: calc(100% + 120px); }
  .race-stats-bar-wrap { width: calc(100% - 24px); }
  .race-banner-stats { grid-template-columns: repeat(2, 1fr); gap: 12px 0; }
  .race-banner-stat:nth-child(2) { border-right: none; }
  .race-banner-stat:nth-child(3) { border-top: 1px solid rgba(255,255,255,.15); padding-top: 12px; }
  .race-banner-stat:nth-child(4) { border-top: 1px solid rgba(255,255,255,.15); padding-top: 12px; }
}
</style>

<div class="race-spaces-page">

  <!-- HERO -->
  <div class="race-hero">
    <img class="race-hero__img"
         src="<?= Html::encode($bannerUrl) ?>"
         alt="Banner" loading="eager" decoding="async">
    <div class="race-hero__overlay"></div>
    <div class="race-hero__cut"></div>

    <div class="race-hero__content">
      <div class="container">
        <div class="race-hero__copy">
          <h1 class="race-hero__title">
            Consultations &amp;<br>
            <em>Sondages Citoyens</em>
          </h1>
          <p class="race-hero__subtitle">
            Participez activement &agrave; la vie d&eacute;mocratique. Explorez les consultations publiques,
            partagez votre opinion et contribuez &agrave; la co-construction de notre soci&eacute;t&eacute;.
          </p>
          <div class="race-actions">
            <a class="race-btn race-btn--primary race-btn--magnetic" href="#race-cards">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
              </svg>
              Explorer les sondages
            </a>
          </div>
        </div>
      </div>
    </div>

  </div><!-- end .race-hero -->

  <!-- SHEET -->
  <div class="race-sheet">
    <div class="container">

      <!-- SECTION BANNER -->
      <div class="race-section-banner race-reveal">
        <div class="race-section-banner__card">
          <h2 class="race-section-banner__title">Consultations &amp; Sondages</h2>
          <?php $actives = ($totalCount !== null) ? (int)$totalCount : 42; ?>
          <div class="race-banner-stats">
            <div class="race-banner-stat">
              <span class="race-banner-stat__value" data-target="70140">0</span>
              <span class="race-banner-stat__label">Participations</span>
            </div>
            <div class="race-banner-stat">
              <span class="race-banner-stat__value" data-target="<?= Html::encode($actives) ?>">0</span>
              <span class="race-banner-stat__label">Consultations</span>
            </div>
            <div class="race-banner-stat">
              <span class="race-banner-stat__value" data-target="70102">0</span>
              <span class="race-banner-stat__label">Contributions</span>
            </div>
            <div class="race-banner-stat">
              <span class="race-banner-stat__value" data-target="421">0</span>
              <span class="race-banner-stat__label">Appr&eacute;ciations</span>
            </div>
          </div>
        </div>
      </div>

      <!-- CARDS -->
      <div id="race-cards" class="row cards" style="margin-top: 10px;">
        <?php if (!$spaces->exists()): ?>
          <div class="col-lg-12">
            <div class="race-empty-state">
              <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
              </svg>
              <strong><?= Yii::t('SpaceModule.base', 'No results found!'); ?></strong>
              <p><?= Yii::t('SpaceModule.base', 'Try other keywords or remove filters.'); ?></p>
            </div>
          </div>
        <?php endif; ?>

        <?php foreach ($spaces->with('contentContainerRecord')->all() as $space) : ?>
          <?= SpaceDirectoryCard::widget(['space' => $space]); ?>
        <?php endforeach; ?>
      </div>

      <?php if (!$spaces->isLastPage()) : ?>
        <?= Html::tag('div', '', [
          'class' => 'cards-end',
          'data-current-page' => $spaces->pagination->getPage() + 1,
          'data-total-pages'  => $spaces->pagination->getPageCount(),
        ]) ?>
      <?php endif; ?>

      <div style="height:140px;"></div>

    </div>
  </div>

</div>