<?php
/**
 * CSEFRS ThemeCitoyen — Spaces Directory (Complete Redesign)
 * Encoding: UTF-8
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

$this->registerCssFile('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap');

$this->registerJs("document.body.classList.add('tc-spaces-page');", View::POS_END);

$bannerUrl = Yii::$app->request->baseUrl . '/themes/ThemeCitoyen/img/banner8.png';

$totalCount = null;
try {
    $totalCount = $spaces->pagination->totalCount ?? null;
} catch (\Throwable $e) {
    $totalCount = null;
}

$this->registerJs(<<<'JS'
(function(){
  /* KILL WHITE GAP — zero out any wrapper padding above hero */
  (function(){
    var hero = document.querySelector('.cs-hero');
    if(!hero) return;
    var el = hero;
    while(el && el !== document.body){
      el = el.parentElement;
      if(el && el !== document.body){
        var st = window.getComputedStyle(el);
        var pt = parseFloat(st.paddingTop)||0;
        var mt = parseFloat(st.marginTop)||0;
        if(pt > 0) el.style.paddingTop = '0';
        if(mt > 0) el.style.marginTop  = '0';
      }
    }
  })();

  /* SMOOTH SCROLL */
  document.querySelectorAll('a[href^="#"]').forEach(function(a){
    a.addEventListener('click',function(e){
      var id=this.getAttribute('href');
      var target=document.querySelector(id);
      if(!target)return;
      e.preventDefault();
      var y=target.getBoundingClientRect().top+(window.scrollY||0)-80;
      window.scrollTo({top:y,behavior:'smooth'});
    });
  });

  /* PARALLAX */
  var heroImg=document.querySelector('.cs-hero__photo');
  if(heroImg){
    var ticking=false;
    window.addEventListener('scroll',function(){
      if(ticking)return; ticking=true;
      requestAnimationFrame(function(){
        ticking=false;
        var y=window.scrollY||0;
        heroImg.style.transform='translate3d(0,'+Math.min(y*0.28,120).toFixed(1)+'px,0)';
      });
    },{passive:true});
  }

  /* SCROLL REVEAL */
  if('IntersectionObserver' in window){
    var io=new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if(e.isIntersecting){e.target.classList.add('cs-revealed');io.unobserve(e.target);}
      });
    },{threshold:0.1});
    document.querySelectorAll('.cs-reveal').forEach(function(el){io.observe(el);});
  } else {
    document.querySelectorAll('.cs-reveal').forEach(function(el){el.classList.add('cs-revealed');});
  }

  /* ANIMATED COUNTERS */
  function animCounter(el){
    var raw=el.getAttribute('data-target')||'';
    var end=parseFloat(raw.replace(/[^0-9.]/g,''));
    if(isNaN(end))return;
    var duration=2000,start=null;
    (function step(t){
      if(!start)start=t;
      var p=Math.min((t-start)/duration,1);
      var ease=1-Math.pow(1-p,4);
      var v=Math.round(end*ease);
      el.textContent=v.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'\u202F');
      if(p<1)requestAnimationFrame(step);
    })(performance.now());
  }
  var cio=new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if(e.isIntersecting){animCounter(e.target);cio.unobserve(e.target);}
    });
  },{threshold:0.6});
  document.querySelectorAll('[data-target]').forEach(function(el){cio.observe(el);});

  /* CONSTELLATION ANIMATION */
  var conNodes  = document.querySelectorAll('.cs-node');
  var conLines  = document.querySelectorAll('.cs-con-line');
  var conVisual = document.getElementById('cs-visual');
  var conSvg    = conVisual ? conVisual.querySelector('.cs-constellation svg') : null;

  if(conNodes.length && conLines.length && conVisual && conSvg){
    /* Give SVG explicit viewBox matching panel */
    function resizeSvg(){
      var wr = conVisual.getBoundingClientRect();
      conSvg.setAttribute('viewBox','0 0 '+wr.width+' '+wr.height);
      conSvg.setAttribute('width', wr.width);
      conSvg.setAttribute('height', wr.height);
    }
    resizeSvg();
    window.addEventListener('resize', resizeSvg, {passive:true});

    var origins = Array.from(conNodes).map(function(n){
      return {
        x:  parseFloat(n.getAttribute('data-ox')),
        y:  parseFloat(n.getAttribute('data-oy')),
        r1: 20 + Math.random()*24,
        r2: 14 + Math.random()*20,
        sp: 0.0003 + Math.random()*0.0005,
        ph: Math.random()*Math.PI*2
      };
    });
    var pairs = [[0,1],[1,2],[2,3],[3,0],[0,2],[1,3]];

    function conTick(){
      var t   = performance.now();
      var wr  = conVisual.getBoundingClientRect();
      if(wr.width === 0){ requestAnimationFrame(conTick); return; }

      /* Move nodes */
      conNodes.forEach(function(n,i){
        var o  = origins[i];
        var nx = o.x + Math.sin(t*o.sp + o.ph)*o.r1;
        var ny = o.y + Math.cos(t*o.sp*0.7 + o.ph)*o.r2;
        n.style.left = nx+'%';
        n.style.top  = ny+'%';
      });

      /* Draw lines using pixel coords relative to visual panel */
      pairs.forEach(function(p,i){
        if(!conLines[i]) return;
        var na = conNodes[p[0]].getBoundingClientRect();
        var nb = conNodes[p[1]].getBoundingClientRect();
        conLines[i].setAttribute('x1', (na.left + na.width/2  - wr.left).toFixed(1));
        conLines[i].setAttribute('y1', (na.top  + na.height/2 - wr.top ).toFixed(1));
        conLines[i].setAttribute('x2', (nb.left + nb.width/2  - wr.left).toFixed(1));
        conLines[i].setAttribute('y2', (nb.top  + nb.height/2 - wr.top ).toFixed(1));
      });

      requestAnimationFrame(conTick);
    }
    /* Small delay so layout is painted before first frame */
    setTimeout(function(){ requestAnimationFrame(conTick); }, 200);
  }

  /* SEARCH + SORT */
  var searchInput = document.getElementById('cs-search-field');
  var sortSelect  = document.getElementById('cs-sort-select');
  var grid        = document.getElementById('cs-cards');

  function getCards(){
    return grid ? Array.from(grid.querySelectorAll('.csf-card')) : [];
  }

  function filterAndSort(){
    var q    = searchInput ? searchInput.value.trim().toLowerCase() : '';
    var sort = sortSelect  ? sortSelect.value : 'recent';
    var cards = getCards();

    cards.forEach(function(card){
      var title = (card.querySelector('.csf-title')||{}).textContent||'';
      var desc  = (card.querySelector('.csf-desc') ||{}).textContent||'';
      var match = !q || title.toLowerCase().indexOf(q) !== -1 || desc.toLowerCase().indexOf(q) !== -1;
      // the card sits inside a col wrapper in some HumHub versions
      var wrapper = card.parentElement && card.parentElement !== grid ? card.parentElement : card;
      wrapper.style.display = match ? '' : 'none';
    });

    /* sort visible cards */
    var visible = getCards().filter(function(c){
      var w = c.parentElement !== grid ? c.parentElement : c;
      return w.style.display !== 'none';
    });

    visible.sort(function(a, b){
      var ta = (a.querySelector('.csf-title')||{}).textContent||'';
      var tb = (b.querySelector('.csf-title')||{}).textContent||'';
      var da = parseInt(a.getAttribute('data-space-id')||'0',10);
      var db = parseInt(b.getAttribute('data-space-id')||'0',10);
      if(sort==='recent')     return db - da;
      if(sort==='oldest')     return da - db;
      if(sort==='alpha')      return ta.localeCompare(tb,'fr');
      if(sort==='alpha-desc') return tb.localeCompare(ta,'fr');
      return 0;
    });

    visible.forEach(function(card){
      var w = card.parentElement !== grid ? card.parentElement : card;
      grid.appendChild(w);
    });
  }

  if(searchInput){ searchInput.addEventListener('input', filterAndSort); }
  if(sortSelect) { sortSelect.addEventListener('change', filterAndSort); }

})();
JS, View::POS_END);
?>

<meta charset="UTF-8">

<style>
/* ================================================================
   CSEFRS SPACES — Complete redesign
   Tone: Refined institutional · editorial · authoritative
   Fonts: Playfair Display (headings) + DM Sans (body)
   Palette: Deep teal #06383B · Crimson #A53535 · Gold #A53535 · Off-white #F8F6F2
================================================================ */

*, *::before, *::after { box-sizing: border-box; }

body.tc-spaces-page {
  font-family: 'DM Sans', sans-serif !important;
  background: #F8F6F2 !important;
  overflow-y: auto !important;
}

/* ── TOKENS ─────────────────────────────────────────── */
.tc-spaces-page {
  --teal:      #06383B;
  --teal-l:    #0b4d51;
  --crimson:   #A53535;
  --crimson-d: #7A1F1F;
  --gold:      #A53535;
  --gold-l:    #A53535;
  --ivory:     #F8F6F2;
  --ivory-d:   #EDE9E2;
  --white:     #ffffff;
  --ink:       #141414;
  --ink-70:    rgba(20,20,20,.70);
  --ink-40:    rgba(20,20,20,.40);
  --border:    rgba(20,20,20,.09);

  --r-xl: 24px;
  --r-lg: 18px;
  --r-md: 12px;
  --r-sm: 8px;
}

/* ── OVERFLOW / LAYOUT RESETS ───────────────────────── */
body.tc-spaces-page #layout-content,
body.tc-spaces-page .layout-content-container,
body.tc-spaces-page .container-cards.container-spaces {
  height: auto !important;
  max-height: none !important;
  overflow: visible !important;
}

/* ── KILL WHITE GAP ABOVE HERO ──────────────────────── */
body.tc-spaces-page,
body.tc-spaces-page #layout-content,
body.tc-spaces-page .layout-content-container,
body.tc-spaces-page .layout-content-container > *:first-child,
body.tc-spaces-page #content-container,
body.tc-spaces-page .container,
body.tc-spaces-page .container-fluid,
body.tc-spaces-page #page-content,
body.tc-spaces-page .stream-container,
body.tc-spaces-page > div:first-child {
  padding-top: 0 !important;
  margin-top: 0 !important;
}
.tc-spaces-page {
  margin-top: 0 !important;
  padding-top: 0 !important;
}
.cs-hero {
  margin-top: 0 !important;
}

/* ── KEYFRAMES ──────────────────────────────────────── */
@keyframes cs-fadeUp   { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:none} }
@keyframes cs-fadeIn   { from{opacity:0} to{opacity:1} }
@keyframes cs-scaleIn  { from{opacity:0;transform:scale(.97)} to{opacity:1;transform:scale(1)} }
@keyframes cs-lineGrow { from{width:0} to{width:100%} }
@keyframes cs-shimmer  { from{background-position:-600px 0} to{background-position:600px 0} }
@keyframes cs-pulse    { 0%,100%{opacity:1} 50%{opacity:.4} }

/* ── SCROLL REVEAL ──────────────────────────────────── */
.cs-reveal {
  opacity:0;
  transform:translateY(24px);
  transition:opacity .7s cubic-bezier(.22,1,.36,1),
             transform .7s cubic-bezier(.22,1,.36,1);
}
.cs-reveal.cs-revealed { opacity:1; transform:none; }
.cs-reveal.d1{transition-delay:.08s} .cs-reveal.d2{transition-delay:.16s}
.cs-reveal.d3{transition-delay:.24s} .cs-reveal.d4{transition-delay:.32s}
.cs-reveal.d5{transition-delay:.40s} .cs-reveal.d6{transition-delay:.48s}

/* ================================================================
   HERO — full-bleed, editorial split layout
================================================================ */
.cs-hero {
  position: relative;
  width: 100vw;
  left: 50%; right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;
  margin-top: -1px;        /* kill any subpixel gap */
  min-height: 680px;
  height: 680px;
  background:
    /* noise texture — subtle grain */
    url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.025'/%3E%3C/svg%3E"),
    /* gentle white top-centre bloom — just a touch */
    radial-gradient(ellipse at 52% 0%,
      rgba(255,255,255,.11) 0%,
      transparent 50%),
    /* soft left rim */
    radial-gradient(ellipse at 0% 45%,
      rgba(255,255,255,.06) 0%,
      transparent 35%),
    /* soft right rim */
    radial-gradient(ellipse at 100% 55%,
      rgba(255,255,255,.05) 0%,
      transparent 35%),
    /* base — deep but not black, rich crimson */
    linear-gradient(145deg,
      #7a1f1f 0%,
      #8f2525 20%,
      #A53535 45%,
      #9a2e2e 65%,
      #7e2222 100%
    );
  overflow: hidden;
  display: flex;
  align-items: stretch;
}

/* Left photo panel */
.cs-hero__photo-wrap {
  position: absolute;
  inset: 0;
  overflow: hidden;
}

.cs-hero__photo {
  position: absolute;
  inset: 0;
  width: 100%; height: 130%;
  top: -15%;
  object-fit: cover;
  object-position: center top;
  opacity: .55;
  filter: grayscale(.15) contrast(1.05) brightness(.85);
  will-change: transform;
  transition: opacity .6s ease;
}

/* layered overlays for depth */
.cs-hero__vignette {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(110deg, rgba(6,56,59,.88) 0%, rgba(6,56,59,.60) 42%, rgba(6,56,59,.18) 75%, transparent 100%),
    linear-gradient(180deg, transparent 45%, rgba(6,56,59,.85) 100%);
  pointer-events: none;
}

/* decorative gold rule top-left */
.cs-hero__rule {
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 3px;
  background: linear-gradient(90deg,
    rgba(255,255,255,.6) 0%,
    rgba(255,255,255,.15) 40%,
    transparent 70%);
}

/* decorative grid pattern */
.cs-hero__grid {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
  background-size: 52px 52px;
  pointer-events: none;
}

.cs-hero__content {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 90px 60px 100px 60px;   /* pushed down 30px */
  display: grid;
  grid-template-columns: 58% 42%;
  align-items: center;
  gap: 0;
  height: 100%;
}

/* Left copy column */
.cs-hero__copy {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding-right: 40px;
}

/* ══════════════════════════════════════════════════════
   RIGHT VISUAL PANEL — artistic constellation + cards
   ══════════════════════════════════════════════════════ */
.cs-hero__visual {
  position: relative;
  height: 100%;
  min-height: 520px;
  overflow: visible;
  /* match text's 30px downward shift */
  transform: translateX(48px) translateY(30px);
}

/* large ambient glow — centred on the card cluster */
.cs-hero__visual::before {
  content: '';
  position: absolute;
  top: 44%; left: 52%;
  transform: translate(-50%,-50%);
  width: 420px; height: 420px;
  border-radius: 50%;
  background: radial-gradient(ellipse,
    rgba(255,255,255,.06) 0%,
    transparent 65%);
  pointer-events: none;
  z-index: 0;
}

/* ── SVG constellation layer (behind cards) ── */
.cs-constellation {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
  pointer-events: none;
  overflow: visible;
}
.cs-constellation svg {
  width: 100%; height: 100%;
  display: block;
  overflow: visible;
}
.cs-con-line {
  stroke: rgba(255,255,255,.2);
  stroke-width: 1;
  stroke-dasharray: 5 7;
  stroke-linecap: round;
}

/* ── Floating nodes ── */
.cs-node {
  position: absolute;
  border-radius: 50%;
  background: rgba(255,255,255,.85);
  transform: translate(-50%,-50%);
  z-index: 2;
  pointer-events: none;
  box-shadow:
    0 0 0 4px rgba(255,255,255,.12),
    0 0 14px rgba(255,255,255,.3);
}
.cs-node--lg { width: 10px; height: 10px; }
.cs-node--md { width: 7px;  height: 7px;  }
.cs-node--sm { width: 4px;  height: 4px;  opacity: .5; }

@keyframes cs-nodein {
  from { opacity:0; transform:translate(-50%,-50%) scale(0); }
  to   { opacity:1; transform:translate(-50%,-50%) scale(1); }
}
.cs-node:nth-child(2) { animation: cs-nodein .6s cubic-bezier(.16,1,.3,1) .3s backwards; }
.cs-node:nth-child(3) { animation: cs-nodein .6s cubic-bezier(.16,1,.3,1) .5s backwards; }
.cs-node:nth-child(4) { animation: cs-nodein .6s cubic-bezier(.16,1,.3,1) .7s backwards; }
.cs-node:nth-child(5) { animation: cs-nodein .6s cubic-bezier(.16,1,.3,1) .9s backwards; }

/* ── Card bob keyframes — gentle, staggered ── */
@keyframes cs-pc1 {
  0%,100% { transform: translateY(0px)   rotate(-2deg);   }
  50%     { transform: translateY(-14px) rotate( .8deg);  }
}
@keyframes cs-pc2 {
  0%,100% { transform: translateY(0px)   rotate( 1.5deg); }
  50%     { transform: translateY(-10px) rotate(-1deg);   }
}
@keyframes cs-pc3 {
  0%,100% { transform: translateY(0px)   rotate( 2.5deg); }
  50%     { transform: translateY(-18px) rotate( .4deg);  }
}
@keyframes cs-pc4 {
  0%,100% { transform: translateY(0px)   rotate(-1deg);   }
  50%     { transform: translateY(-12px) rotate( 1.5deg); }
}

/* ── Cards base style ── */
.cs-pcard {
  position: absolute;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid rgba(255,255,255,.14);
  box-shadow:
    0 8px 32px rgba(0,0,0,.3),
    0 2px 8px  rgba(0,0,0,.15),
    0 0 0 1px rgba(255,255,255,.12),
    inset 0 1px 0 rgba(255,255,255,.15);
  z-index: 4;
  pointer-events: auto;
  transition: transform .3s cubic-bezier(.22,1,.36,1),
              box-shadow .3s ease;
}
.cs-pcard:hover {
  transform: translateY(-6px) scale(1.03) !important;
  box-shadow:
    0 16px 48px rgba(0,0,0,.35),
    0 0 0 1px rgba(255,255,255,.2),
    0 0 28px rgba(255,255,255,.08),
    inset 0 1px 0 rgba(255,255,255,.2);
}
.cs-pcard img {
  width: 100%; height: 100%;
  object-fit: cover;
  filter: brightness(.72) saturate(.45) contrast(1.05);
  display: block;
  transition: filter .45s ease;
}
.cs-pcard:hover img {
  filter: brightness(.88) saturate(.72) contrast(1.05);
}
.cs-pcard::after {
  content: '';
  position: absolute;
  inset: 0;
  border: 2px solid rgba(165,53,53,0);
  border-radius: 10px;
  transition: border-color .3s ease;
  pointer-events: none;
  z-index: 5;
}
.cs-pcard:hover::after { border-color: rgba(165,53,53,.55); }

.cs-pcard__label {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  padding: 20px 12px 10px;
  background: linear-gradient(transparent, rgba(0,0,0,.82));
  font-family: 'DM Sans', sans-serif;
  font-size: 8.5px;
  font-weight: 700;
  letter-spacing: .22em;
  text-transform: uppercase;
  color: rgba(255,255,255,.78);
  z-index: 5;
}

/* ── CARD LAYOUT — intentional editorial composition ──
   Think magazine spread: big card top-left, tall card top-right,
   medium bottom-left offset, square centre overlap.
   All contained within the 45% right column.
─────────────────────────────────────────────────────── */

/* Card 1 — large portrait, top-left of cluster */
.cs-pcard--1 {
  width: 185px; height: 230px;
  top: 6%; left: 0%;
  animation: cs-pc1 10s ease-in-out infinite;
}
/* Card 2 — tall portrait, top-right, offset up */
.cs-pcard--2 {
  width: 148px; height: 185px;
  top: 2%; right: 4%;
  animation: cs-pc2 13s ease-in-out infinite;
}
/* Card 3 — landscape, bottom-left, peeks below card 1 */
.cs-pcard--3 {
  width: 172px; height: 148px;
  bottom: 10%; left: 5%;
  animation: cs-pc3  9s ease-in-out infinite;
}
/* Card 4 — square, centre overlap — bridges left & right */
.cs-pcard--4 {
  width: 162px; height: 155px;
  top: 42%; left: 36%;
  animation: cs-pc4 11s ease-in-out infinite;
}

@media (max-width: 1024px) {
  .cs-hero { height: auto; min-height: 640px; }
  .cs-hero__content { grid-template-columns: 1fr !important; padding: 72px 32px 160px !important; height: auto; }
  .cs-hero__copy { padding-right: 0 !important; }
  .cs-hero__visual { display: none; }
}

/* Institution badge */
.cs-hero__badge {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 7px 16px 7px 10px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.22);
  background: rgba(0,0,0,.25);
  color: rgba(255,255,255,.85);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: .12em;
  text-transform: uppercase;
  width: fit-content;
  margin-bottom: 28px;
  animation: cs-fadeUp .8s cubic-bezier(.22,1,.36,1) .1s both;
}

.cs-hero__badge-dot {
  width: 7px; height: 7px;
  border-radius: 50%;
  background: rgba(255,255,255,.8);
  animation: cs-pulse 2s ease infinite;
}

/* Main heading */
.cs-hero__eyebrow {
  font-family: 'DM Sans', sans-serif;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .25em;
  text-transform: uppercase;
  color: rgba(255,255,255,.45);
  margin-bottom: 16px;
  animation: cs-fadeUp .8s cubic-bezier(.22,1,.36,1) .2s both;
}

.cs-hero__title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(36px, 4vw, 58px);
  font-weight: 900;
  line-height: 1.08;
  letter-spacing: -.3px;
  color: #fff;
  margin: 0 0 20px 0;
  /* each line handled individually */
  animation: cs-fadeUp .8s cubic-bezier(.22,1,.36,1) .25s both;
}

.cs-hero__title em {
  font-style: italic;
  color: #ffffff;
  display: block;
  white-space: nowrap;
}

/* Decorative rule under title */
.cs-hero__title-rule {
  width: 80px; height: 3px;
  background: #A53535;
  border-radius: 999px;
  margin-bottom: 28px;
  animation: cs-lineGrow .8s cubic-bezier(.22,1,.36,1) .45s both;
}

.cs-hero__subtitle {
  font-size: clamp(15px, 1.6vw, 17px);
  font-weight: 400;
  color: rgba(255,255,255,.62);
  line-height: 1.75;
  max-width: 580px;
  margin: 0 0 40px 0;
  animation: cs-fadeUp .8s cubic-bezier(.22,1,.36,1) .35s both;
}

.cs-hero__cta-row {
  display: flex;
  gap: 14px;
  align-items: center;
  flex-wrap: wrap;
  animation: cs-fadeUp .8s cubic-bezier(.22,1,.36,1) .45s both;
}

.cs-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  height: 52px;
  padding: 0 28px;
  border-radius: var(--r-md);
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none !important;
  cursor: pointer;
  border: none;
  position: relative;
  overflow: hidden;
  transition: transform .22s cubic-bezier(.22,1,.36,1), box-shadow .22s ease;
}

.cs-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0);
  transition: background .2s ease;
}
.cs-btn:hover::before { background: rgba(255,255,255,.1); }

.cs-btn--primary {
  background: transparent;
  border: 1.5px solid rgba(255,255,255,.5) !important;
  color: #fff !important;
  box-shadow: none;
}
.cs-btn--primary:hover {
  transform: translateY(-3px);
  border-color: rgba(255,255,255,.85) !important;
  background: rgba(255,255,255,.08);
  box-shadow: none;
}

.cs-btn--ghost {
  background: transparent;
  border: 1.5px solid rgba(255,255,255,.25) !important;
  color: rgba(255,255,255,.85) !important;
}
.cs-btn--ghost:hover {
  transform: translateY(-3px);
  border-color: rgba(255,255,255,.5) !important;
  background: rgba(255,255,255,.07);
}

/* ── STATS RIBBON ──────────────────────────────────── */
.cs-hero__stats {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  z-index: 3;
}

.cs-stats-ribbon {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 60px;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
}

.cs-stat-pill {
  padding: 20px 28px;
  border-top: 1px solid rgba(255,255,255,.08);
  border-right: 1px solid rgba(255,255,255,.06);
  background: rgba(0,0,0,.35);
  backdrop-filter: blur(20px);
  transition: background .2s ease;
}
.cs-stat-pill:first-child { border-left: 1px solid rgba(255,255,255,.06); }
.cs-stat-pill:hover { background: rgba(0,0,0,.5); }

.cs-stat-pill__num {
  font-family: 'Playfair Display', serif;
  font-size: 28px;
  font-weight: 700;
  color: #fff;
  line-height: 1;
  margin-bottom: 4px;
  letter-spacing: -.5px;
}

.cs-stat-pill__label {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: rgba(255,255,255,.4);
}

/* straight hero bottom edge — no cut */
.cs-hero__cut {
  display: none;
}

/* ================================================================
   MAIN SHEET
================================================================ */
.cs-sheet {
  background: var(--ivory);
  position: relative;
  z-index: 4;
  padding-bottom: 120px;
}

.cs-sheet__inner {
  max-width: 1240px;
  margin: 0 auto;
  padding: 0 40px;
}

/* ── SECTION HEADER ─────────────────────────────────── */
.cs-section-header {
  padding: 52px 0 36px;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  border-bottom: 1px solid var(--border);
  margin-bottom: 36px;
}

.cs-section-header__left {}

.cs-section-header__eyebrow {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: .22em;
  text-transform: uppercase;
  color: var(--crimson);
  margin-bottom: 10px;
}

.cs-section-header__eyebrow::before {
  content: '';
  width: 22px; height: 2px;
  background: var(--crimson);
  border-radius: 999px;
}

.cs-section-header__title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(28px, 3vw, 40px);
  font-weight: 900;
  color: var(--ink);
  letter-spacing: -.3px;
  line-height: 1.1;
  margin: 0;
}

.cs-section-header__count {
  font-family: 'DM Sans', sans-serif;
  font-size: 13px;
  font-weight: 500;
  color: var(--ink-40);
  padding-bottom: 6px;
  white-space: nowrap;
}

.cs-section-header__count strong {
  font-size: 20px;
  font-weight: 800;
  color: var(--crimson);
  font-family: 'Playfair Display', serif;
}

/* ── SEARCH + SORT TOOLBAR ───────────────────────────── */
.cs-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 32px;
  flex-wrap: wrap;
}

.cs-search-wrap {
  flex: 0 0 auto;
  width: 380px;
  max-width: 55%;
  position: relative;
  display: flex;
  align-items: stretch;
  border: 1.5px solid var(--border);
  border-radius: 6px;
  overflow: hidden;
  background: var(--white);
  box-shadow: 0 1px 4px rgba(0,0,0,.05);
  transition: border-color .2s ease, box-shadow .2s ease;
}

.cs-search-wrap:focus-within {
  border-color: #A53535;
  box-shadow: 0 0 0 3px rgba(165,53,53,.12);
}

.cs-search-input {
  flex: 1;
  height: 44px;
  padding: 0 14px;
  border: none;
  background: transparent;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  font-weight: 400;
  color: var(--ink);
  outline: none;
  -webkit-appearance: none;
  min-width: 0;
}

.cs-search-input::placeholder {
  color: var(--ink-40);
}

.cs-search-btn {
  flex-shrink: 0;
  width: 46px;
  height: 44px;
  background: #A53535;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background .18s ease;
  padding: 0;
}

.cs-search-btn:hover { background: #8b2c2c; }
.cs-search-btn:active { background: #7a1f1f; }

.cs-search-btn svg {
  width: 16px; height: 16px;
  color: #fff;
  flex-shrink: 0;
}

/* hide old icon span */
.cs-search-wrap .cs-search-icon { display: none; }

.cs-sort-wrap {
  flex-shrink: 0;
}

.cs-sort-select {
  height: 44px;
  padding: 0 38px 0 14px;
  border-radius: 8px;
  border: 1.5px solid var(--border);
  background: var(--white);
  font-family: 'DM Sans', sans-serif;
  font-size: 13.5px;
  font-weight: 500;
  color: var(--ink);
  outline: none;
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 11px center;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
  transition: border-color .2s ease, box-shadow .2s ease;
  min-width: 200px;
}

.cs-sort-select:focus {
  border-color: var(--teal);
  box-shadow: 0 0 0 3px rgba(6,56,59,.09);
}

@media (max-width: 600px) {
  .cs-toolbar { flex-direction: column; align-items: stretch; }
  .cs-search-wrap { width: 100%; }
  .cs-sort-select { min-width: 0; width: 100%; }
}

/* ── CARDS GRID ──────────────────────────────────────── */
#cs-cards.row {
  display: grid !important;
  grid-template-columns: repeat(3, 1fr) !important;
  gap: 28px !important;
  margin: 0 !important;
  float: none !important;
}

/* reset humhub col classes inside grid */
#cs-cards .col-md-4,
#cs-cards [class*="col-"] {
  width: 100% !important;
  padding: 0 !important;
  float: none !important;
}

@media (max-width: 1100px) {
  #cs-cards.row { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 680px) {
  #cs-cards.row { grid-template-columns: 1fr !important; gap: 20px !important; }
}

/* ── EMPTY STATE ─────────────────────────────────────── */
.cs-empty {
  grid-column: 1 / -1;
  text-align: center;
  padding: 72px 32px;
  background: var(--white);
  border-radius: var(--r-xl);
  border: 1.5px dashed var(--border);
}
.cs-empty svg { margin-bottom: 16px; color: var(--ink-40); }
.cs-empty strong { display: block; font-size: 18px; font-weight: 800; color: var(--ink); margin-bottom: 8px; }
.cs-empty p { color: var(--ink-70); font-size: 14px; margin: 0; }

/* ── DECORATIVE ASIDE ──────────────────────────────── */
.cs-mandate-strip {
  background:
    radial-gradient(ellipse at 50% 0%,
      rgba(255,255,255,.09) 0%,
      transparent 55%),
    radial-gradient(ellipse at 0% 50%,
      rgba(255,255,255,.05) 0%,
      transparent 35%),
    radial-gradient(ellipse at 100% 55%,
      rgba(255,255,255,.04) 0%,
      transparent 35%),
    linear-gradient(145deg,
      #7a1f1f 0%,
      #8f2525 20%,
      #A53535 45%,
      #9a2e2e 65%,
      #7e2222 100%
    );
  border-radius: 12px;
  padding: 48px 56px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
  margin: 60px 0 0;
  position: relative;
  overflow: hidden;
}

.cs-mandate-strip::before {
  content: '';
  position: absolute;
  right: -60px; top: -60px;
  width: 320px; height: 320px;
  border-radius: 50%;
  border: 40px solid rgba(255,255,255,.04);
  pointer-events: none;
}

.cs-mandate-strip::after {
  content: '';
  position: absolute;
  right: 80px; bottom: -80px;
  width: 240px; height: 240px;
  border-radius: 50%;
  border: 30px solid rgba(255,255,255,.03);
  pointer-events: none;
}

.cs-mandate-strip__left { position: relative; z-index: 1; }

.cs-mandate-strip__tag {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .22em;
  text-transform: uppercase;
  color: rgba(255,255,255,.65);
  margin-bottom: 12px;
}

.cs-mandate-strip__title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(22px, 2.5vw, 32px);
  font-weight: 700;
  color: #fff;
  margin: 0 0 14px 0;
  max-width: 480px;
  line-height: 1.2;
}

.cs-mandate-strip__body {
  font-size: 14px;
  color: rgba(255,255,255,.55);
  line-height: 1.7;
  max-width: 460px;
  margin: 0;
}

.cs-mandate-strip__right {
  position: relative; z-index: 1;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 16px;
}

.cs-mandate-num {
  text-align: center;
  padding: 20px 28px;
  background: rgba(255,255,255,.06);
  border-radius: var(--r-lg);
  border: 1px solid rgba(255,255,255,.08);
}

.cs-mandate-num__val {
  font-family: 'Playfair Display', serif;
  font-size: 36px;
  font-weight: 900;
  color: #fff;
  line-height: 1;
  margin-bottom: 4px;
}

.cs-mandate-num__lbl {
  font-size: 9.5px;
  font-weight: 700;
  letter-spacing: .16em;
  text-transform: uppercase;
  color: rgba(255,255,255,.35);
}

/* ── RESPONSIVE ──────────────────────────────────────── */
@media (max-width: 992px) {
  .cs-hero { height: auto; }
  .cs-hero__content { padding: 60px 24px 160px !important; grid-template-columns: 1fr !important; }
  .cs-sheet__inner { padding: 0 24px; }
  .cs-stats-ribbon { padding: 0 24px; grid-template-columns: repeat(2, 1fr); }
  .cs-stat-pill:nth-child(3) { border-left: 1px solid rgba(255,255,255,.06); }
  .cs-mandate-strip { flex-direction: column; padding: 36px 32px; }
  .cs-mandate-strip__right { flex-direction: row; justify-content: flex-start; }
}

@media (max-width: 768px) {
  .cs-hero { min-height: 560px; height: auto; }
  .cs-hero__content { padding: 60px 20px 180px !important; }
  .cs-stats-ribbon { grid-template-columns: repeat(2, 1fr); padding: 0 20px; }
  .cs-section-header { flex-direction: column; align-items: flex-start; }
  .cs-sheet__inner { padding: 0 16px; }
  .cs-mandate-strip { padding: 28px 24px; }
  .cs-mandate-strip__right { flex-wrap: wrap; }
}

@media (max-width: 480px) {
  .cs-stats-ribbon { grid-template-columns: 1fr 1fr; }
  .cs-stat-pill { padding: 14px 16px; }
  .cs-stat-pill__num { font-size: 22px; }
}
</style>

<div class="tc-spaces-page">

<!-- ================================================================
     HERO
================================================================ -->
<div class="cs-hero">

  <!-- no background photo — pure dark teal -->

  <div class="cs-hero__grid" aria-hidden="true"></div>
  <div class="cs-hero__rule" aria-hidden="true"></div>

  <!-- main copy — two-column grid: left text, right constellation -->
  <div class="cs-hero__content">

    <!-- LEFT: text copy -->
    <div class="cs-hero__copy">

      <p class="cs-hero__eyebrow">Plateforme citoyenne participative</p>

      <h1 class="cs-hero__title">
        Consultations &amp;<br><em>Sondages Citoyens</em>
      </h1>

      <div class="cs-hero__title-rule"></div>

      <p class="cs-hero__subtitle">
        Participez activement à la construction des politiques éducatives nationales.
        Explorez les consultations publiques et faites entendre votre voix.
      </p>

      <div class="cs-hero__cta-row">
        <a href="#cs-cards" class="cs-btn cs-btn--primary">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          Explorer les sondages
        </a>
        <a href="#cs-cards" class="cs-btn cs-btn--ghost">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Participer
        </a>
      </div>

    </div><!-- end .cs-hero__copy -->

    <!-- RIGHT: constellation + floating picture cards -->
    <div class="cs-hero__visual" id="cs-visual">

      <!-- SVG canvas for animated connecting lines -->
      <div class="cs-constellation" id="cs-constellation">
        <svg xmlns="http://www.w3.org/2000/svg">
          <line class="cs-con-line" x1="0" y1="0" x2="0" y2="0"/>
          <line class="cs-con-line" x1="0" y1="0" x2="0" y2="0"/>
          <line class="cs-con-line" x1="0" y1="0" x2="0" y2="0"/>
          <line class="cs-con-line" x1="0" y1="0" x2="0" y2="0"/>
          <line class="cs-con-line" x1="0" y1="0" x2="0" y2="0"/>
          <line class="cs-con-line" x1="0" y1="0" x2="0" y2="0"/>
        </svg>
      </div>

      <!-- Animated nodes (floating dots) -->
      <!-- nodes placed near card corners for natural line connections -->
      <div class="cs-node cs-node--lg" data-ox="22" data-oy="30"  style="left:22%;top:30%;"></div>
      <div class="cs-node cs-node--md" data-ox="74" data-oy="18"  style="left:74%;top:18%;"></div>
      <div class="cs-node cs-node--sm" data-ox="18" data-oy="72"  style="left:18%;top:72%;"></div>
      <div class="cs-node cs-node--md" data-ox="60" data-oy="58"  style="left:60%;top:58%;"></div>

      <!-- Floating picture cards -->
      <div class="cs-pcard cs-pcard--1">
        <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?w=400&q=75&auto=format&fit=crop" alt="">
        <div class="cs-pcard__label">Éducation</div>
      </div>
      <div class="cs-pcard cs-pcard--2">
        <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=400&q=75&auto=format&fit=crop" alt="">
        <div class="cs-pcard__label">Avenir</div>
      </div>
      <div class="cs-pcard cs-pcard--3">
        <img src="https://images.unsplash.com/photo-1532012197267-da84d127e765?w=400&q=75&auto=format&fit=crop" alt="">
        <div class="cs-pcard__label">Formation</div>
      </div>
      <div class="cs-pcard cs-pcard--4">
        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=75&auto=format&fit=crop" alt="">
        <div class="cs-pcard__label">Recherche</div>
      </div>

    </div><!-- end .cs-hero__visual -->

  </div>

  <!-- stats ribbon -->
  <?php $actives = ($totalCount !== null) ? (int)$totalCount : 42; ?>
  <div class="cs-hero__stats">
    <div class="cs-stats-ribbon">

      <div class="cs-stat-pill">
        <div class="cs-stat-pill__num" data-target="70140">0</div>
        <div class="cs-stat-pill__label">Participations</div>
      </div>

      <div class="cs-stat-pill">
        <div class="cs-stat-pill__num" data-target="<?= Html::encode($actives) ?>">0</div>
        <div class="cs-stat-pill__label">Consultations actives</div>
      </div>

      <div class="cs-stat-pill">
        <div class="cs-stat-pill__num" data-target="70102">0</div>
        <div class="cs-stat-pill__label">Contributions</div>
      </div>

      <div class="cs-stat-pill">
        <div class="cs-stat-pill__num" data-target="421">0</div>
        <div class="cs-stat-pill__label">Appréciations</div>
      </div>

    </div>
  </div>

  <div class="cs-hero__cut" aria-hidden="true"></div>
</div><!-- end .cs-hero -->


<!-- ================================================================
     MAIN CONTENT SHEET
================================================================ -->
<div class="cs-sheet">
  <div class="cs-sheet__inner">

    <!-- Section header -->
    <div class="cs-section-header cs-reveal">
      <div class="cs-section-header__left">
        <div class="cs-section-header__eyebrow">Formulaires &amp; Consultations</div>
        <h2 class="cs-section-header__title">Espaces de participation</h2>
      </div>
      <?php if ($totalCount !== null): ?>
      <div class="cs-section-header__count">
        <strong><?= Html::encode($totalCount) ?></strong> consultation<?= $totalCount > 1 ? 's' : '' ?> disponible<?= $totalCount > 1 ? 's' : '' ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Search + Sort toolbar -->
    <div class="cs-toolbar cs-reveal d1">

      <div class="cs-search-wrap">
        <input
          type="search"
          class="cs-search-input"
          placeholder="Que cherchez-vous ?"
          id="cs-search-field"
          autocomplete="off"
          aria-label="Rechercher une consultation"
        >
        <button class="cs-search-btn" type="button" aria-label="Rechercher">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </button>
      </div>

      <div class="cs-sort-wrap">
        <select class="cs-sort-select" id="cs-sort-select" aria-label="Trier les consultations">
          <option value="recent">Plus récentes d'abord</option>
          <option value="oldest">Plus anciennes d'abord</option>
          <option value="alpha">Ordre alphabétique</option>
          <option value="alpha-desc">Ordre alphabétique inversé</option>
        </select>
      </div>

    </div>

    <!-- Cards grid -->
    <div id="cs-cards" class="row cards cs-reveal d2">

      <?php if (!$spaces->exists()): ?>
        <div class="cs-empty">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <strong><?= Yii::t('SpaceModule.base', 'No results found!') ?></strong>
          <p><?= Yii::t('SpaceModule.base', 'Try other keywords or remove filters.') ?></p>
        </div>
      <?php endif; ?>

      <?php foreach ($spaces->with('contentContainerRecord')->all() as $space): ?>
        <?= SpaceDirectoryCard::widget(['space' => $space]); ?>
      <?php endforeach; ?>

    </div>

    <?php if (!$spaces->isLastPage()): ?>
      <?= Html::tag('div', '', [
        'class'              => 'cards-end',
        'data-current-page'  => $spaces->pagination->getPage() + 1,
        'data-total-pages'   => $spaces->pagination->getPageCount(),
      ]) ?>
    <?php endif; ?>

    <!-- Mandate strip -->
    <div class="cs-mandate-strip cs-reveal">
      <div class="cs-mandate-strip__left">
        <div class="cs-mandate-strip__tag">Notre impact</div>
        <h3 class="cs-mandate-strip__title">Une voix nationale pour l'éducation de demain</h3>
        <p class="cs-mandate-strip__body">
          Chaque consultation lancée par le CSEFRS engage citoyens, enseignants
          et familles dans la co-construction des grandes réformes éducatives du Royaume.
          Votre participation compte.
        </p>
      </div>

    </div>

    <div style="height:60px;"></div>

  </div>
</div>

</div><!-- end .tc-spaces-page -->