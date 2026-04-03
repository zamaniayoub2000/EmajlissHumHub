<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\Login;
use humhub\modules\user\models\Invite;
use humhub\modules\user\widgets\AuthChoice;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\CaptchaField;
use humhub\widgets\LanguageChooser;
use humhub\widgets\SiteLogo;

$this->pageTitle = Yii::t('UserModule.auth', 'Login');
$this->registerCssFile('https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap');

$this->registerCss(<<<CSS
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

:root{
  --crimson:   #A53535;
  --crimson-h: #c04040;
  --crimson-d: #8b2c2c;
  --glow:      rgba(165,53,53,0.14);
  --bg:        #f5f3f0;
  --bg2:       #ede9e4;
  --white:     #ffffff;
  --surface:   #ffffff;
  --border:    #e8e3dc;
  --border-h:  rgba(165,53,53,0.35);
  --text:      #1c1917;
  --text2:     #6b6560;
  --text3:     #a8a098;
  --input-bg:  #faf9f7;
  --input-focus:#fff8f8;
  --radius:    16px;
  --radius-sm: 10px;
}

html,body{height:100%;background:var(--bg)!important;color:var(--text);font-family:'DM Sans',sans-serif!important;}

/* ── BACKGROUND ── */
#l-bg{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none;}
.l-orb{position:absolute;border-radius:50%;filter:blur(90px);}
.l-orb-1{
  width:700px;height:700px;
  background:radial-gradient(circle, rgba(165,53,53,0.09) 0%, transparent 65%);
  top:-250px;left:-200px;
  animation:orbFloat1 20s ease-in-out infinite;
}
.l-orb-2{
  width:600px;height:600px;
  background:radial-gradient(circle, rgba(165,53,53,0.06) 0%, transparent 65%);
  bottom:-200px;right:-180px;
  animation:orbFloat2 25s ease-in-out infinite;
}
.l-orb-3{
  width:400px;height:400px;
  background:radial-gradient(circle, rgba(200,120,80,0.05) 0%, transparent 70%);
  top:40%;left:55%;
  animation:orbPulse 12s ease-in-out infinite;
}
.l-orb-4{display:none;}
#l-bg::before{
  content:'';position:absolute;inset:0;
  background-image:radial-gradient(circle, rgba(165,53,53,0.07) 1px, transparent 1px);
  background-size:32px 32px;
  mask-image:radial-gradient(ellipse 70% 70% at 50% 50%, black 20%, transparent 100%);
}
.l-bg-dots{display:none;}
.l-bg-streak{display:none;}
@keyframes orbFloat1{0%,100%{transform:translate(0,0);}50%{transform:translate(50px,35px);}}
@keyframes orbFloat2{0%,100%{transform:translate(0,0);}50%{transform:translate(-40px,-25px);}}
@keyframes orbPulse{0%,100%{opacity:.5;transform:scale(1);}50%{opacity:1;transform:scale(1.15);}}

/* ── BACKGROUND LOGO — roams widely around the forms, drifting far out ── */
/*
  Positioned absolute inside #user-auth-login (centered on screen).
  Large translate values push it well outside the card boundaries,
  but it always passes back through center, so it's "hiding behind" the
  forms for part of the cycle and clearly visible outside them for the rest.
*/
.l-bg-logo{
  position:absolute;
  width:520px;
  height:520px;
  pointer-events:none;
  z-index:0;
  opacity:.00;
  filter:
    brightness(0) saturate(100%)
    invert(22%) sepia(60%) saturate(700%)
    hue-rotate(320deg) brightness(0.85);
  object-fit:contain;
  top:50%;
  left:50%;
  transform:translate(-50%, -50%);
  animation:bgLogoWide 34s ease-in-out infinite;
  will-change:transform;
}

/*
  Translate values use px so the logo moves far relative to its own size.
  Positive/negative combos send it to all four quadrants and corners,
  with large offsets (~300-400px) that place it well outside the card area,
  then it glides back through center, briefly hiding behind the forms.
*/
@keyframes bgLogoWide {
  0%   { transform: translate(-50%, -50%) rotate(-4deg)  scale(1);    }
  10%  { transform: translate(calc(-50% + 340px), calc(-50% - 180px)) rotate(6deg)   scale(1.05); }
  22%  { transform: translate(calc(-50% + 280px), calc(-50% + 300px)) rotate(12deg)  scale(0.96); }
  35%  { transform: translate(calc(-50% - 60px),  calc(-50% - 20px))  rotate(-2deg)  scale(1.02); }
  47%  { transform: translate(calc(-50% - 360px), calc(-50% + 160px)) rotate(-10deg) scale(1.06); }
  58%  { transform: translate(calc(-50% - 220px), calc(-50% - 280px)) rotate(5deg)   scale(0.97); }
  70%  { transform: translate(calc(-50% + 40px),  calc(-50% + 30px))  rotate(-3deg)  scale(1.01); }
  82%  { transform: translate(calc(-50% + 300px), calc(-50% - 250px)) rotate(9deg)   scale(1.04); }
  92%  { transform: translate(calc(-50% - 180px), calc(-50% + 220px)) rotate(-7deg)  scale(0.98); }
  100% { transform: translate(-50%, -50%) rotate(-4deg)  scale(1);    }
}

/* ── DECORATIVE SHAPES (subtle red) ── */
.l-deco-line-top{
  position:fixed;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg, transparent 0%, rgba(165,53,53,0.25) 30%, rgba(165,53,53,0.45) 50%, rgba(165,53,53,0.25) 70%, transparent 100%);
  pointer-events:none;z-index:0;
}
.l-deco-line-left{
  position:fixed;top:0;left:0;bottom:0;width:2px;
  background:linear-gradient(180deg, transparent 0%, rgba(165,53,53,0.2) 35%, rgba(165,53,53,0.35) 55%, rgba(165,53,53,0.15) 80%, transparent 100%);
  pointer-events:none;z-index:0;
}
.l-deco-diamond{
  position:fixed;width:10px;height:10px;
  background:rgba(165,53,53,0.15);transform:rotate(45deg);
  pointer-events:none;z-index:0;border-radius:2px;
}
.l-deco-diamond.d1{top:18%;left:4%;animation:diamondPulse 5s ease-in-out infinite;}
.l-deco-diamond.d2{top:72%;left:2.5%;width:6px;height:6px;animation:diamondPulse 7s ease-in-out .8s infinite;}
.l-deco-diamond.d3{top:12%;right:3.5%;animation:diamondPulse 6s ease-in-out .4s infinite;}
.l-deco-diamond.d4{bottom:20%;right:2%;width:7px;height:7px;animation:diamondPulse 8s ease-in-out 1.2s infinite;}
@keyframes diamondPulse{0%,100%{opacity:.15;transform:rotate(45deg) scale(1);}50%{opacity:.35;transform:rotate(45deg) scale(1.3);}}
.l-deco-cross{
  position:fixed;pointer-events:none;z-index:0;
  color:rgba(165,53,53,0.2);font-size:14px;line-height:1;font-weight:300;font-family:sans-serif;
}
.l-deco-cross.c1{top:38%;left:1.5%;}
.l-deco-cross.c2{top:55%;right:1.8%;font-size:11px;}
.l-deco-cross.c3{bottom:35%;left:3%;}

/* ── EXTRA RED DECORATIVE SHAPES ── */
.l-deco-arc-tl{
  position:fixed;top:-60px;left:-60px;
  width:260px;height:260px;border-radius:50%;
  border:1.5px solid rgba(165,53,53,0.12);
  pointer-events:none;z-index:0;
}
.l-deco-arc-tl::after{
  content:'';position:absolute;inset:24px;border-radius:50%;
  border:1px solid rgba(165,53,53,0.07);
}
.l-deco-arc-br{
  position:fixed;bottom:-80px;right:-80px;
  width:320px;height:320px;border-radius:50%;
  border:1.5px solid rgba(165,53,53,0.10);
  pointer-events:none;z-index:0;
}
.l-deco-arc-br::after{
  content:'';position:absolute;inset:28px;border-radius:50%;
  border:1px solid rgba(165,53,53,0.1);
}

/* ── EXTRA RED DECORATIVE SHAPES ── */
.l-deco-qc-tr{
  position:fixed;top:-120px;right:-120px;
  width:380px;height:380px;border-radius:50%;
  border:1px solid rgba(165,53,53,0.09);
  pointer-events:none;z-index:0;
}
.l-deco-qc-tr::before{
  content:'';position:absolute;inset:40px;border-radius:50%;
  border:1px solid rgba(165,53,53,0.03);
}
.l-deco-qc-tr::after{
  content:'';position:absolute;inset:80px;border-radius:50%;
  border:1px solid rgba(165,53,53,0.03);
}
.l-deco-hline-mid{
  position:fixed;top:50%;left:0;right:0;height:1px;
  background:linear-gradient(90deg,
    transparent 0%,
    rgba(165,53,53,0.07) 15%,
    rgba(165,53,53,0.13) 35%,
    rgba(165,53,53,0.05) 50%,
    rgba(165,53,53,0.13) 65%,
    rgba(165,53,53,0.07) 85%,
    transparent 100%);
  pointer-events:none;z-index:0;
}
.l-deco-vline-right{
  position:fixed;top:0;right:0;bottom:0;width:1px;
  background:linear-gradient(180deg,
    transparent 0%,
    rgba(165,53,53,0.1) 25%,
    rgba(165,53,53,0.2) 50%,
    rgba(165,53,53,0.1) 75%,
    transparent 100%);
  pointer-events:none;z-index:0;
}
.l-deco-slash{
  position:fixed;pointer-events:none;z-index:0;
  bottom:80px;left:40px;
  width:120px;height:1px;
  background:rgba(165,53,53,0.12);
  transform:rotate(-35deg);
  transform-origin:left center;
}
.l-deco-slash::before{
  content:'';position:absolute;top:-14px;left:10px;
  width:90px;height:1px;
  background:rgba(165,53,53,0.07);
}
.l-deco-slash::after{
  content:'';position:absolute;top:14px;left:20px;
  width:70px;height:1px;
  background:rgba(165,53,53,0.05);
}
.l-deco-dot{
  position:fixed;border-radius:50%;
  pointer-events:none;z-index:0;
  background:rgba(165,53,53,0.18);
}
.l-deco-dot.dot1{width:4px;height:4px;top:22%;right:8%;}
.l-deco-dot.dot2{width:3px;height:3px;top:75%;left:8%;opacity:.5;}
.l-deco-dot.dot3{width:5px;height:5px;bottom:15%;right:12%;opacity:.6;}
.l-deco-dot.dot4{width:3px;height:3px;top:42%;left:7%;opacity:.55;}
.l-deco-dot.dot5{width:4px;height:4px;top:10%;right:35%;opacity:.4;}
.l-deco-bracket{
  position:fixed;top:18%;right:5%;
  width:22px;height:36px;
  border-top:1.5px solid rgba(165,53,53,0.15);
  border-right:1.5px solid rgba(165,53,53,0.15);
  border-radius:0 6px 0 0;
  pointer-events:none;z-index:0;
}
.l-deco-bracket-bl{
  position:fixed;bottom:22%;left:4%;
  width:22px;height:36px;
  border-bottom:1.5px solid rgba(165,53,53,0.15);
  border-left:1.5px solid rgba(165,53,53,0.15);
  border-radius:0 0 0 6px;
  pointer-events:none;z-index:0;
}

/* PAGE */

#user-auth-login{
  position:relative;z-index:1;
  min-height:100vh!important;
  width:100%!important;max-width:100%!important;
  display:flex!important;flex-direction:column!important;
  align-items:center!important;justify-content:center!important;
  padding:16px 24px 32px!important;
  box-sizing:border-box!important;
  /* needed so absolute-positioned bg logo is relative to this container */
  overflow:hidden;
}

/* ── HEADER ── */
.l-header{
  display:flex;flex-direction:column;align-items:center;
  margin-bottom:18px;
  opacity:0;animation:fadeUp 1s cubic-bezier(.16,1,.3,1) .05s forwards;
  position:relative;z-index:2;
}

/* LOGO — bigger */
.l-logo-wrap{position:relative;width:120px;height:120px;margin-bottom:18px;}
.l-logo-wrap::before{
  content:'';position:absolute;inset:-10px;
  border-radius:34px;
  background:conic-gradient(from 0deg, var(--crimson) 0%, rgba(165,53,53,0.1) 45%, var(--crimson) 100%);
  animation:spinRing 7s linear infinite;
  opacity:.32;
}
.l-logo-wrap::after{
  content:'';position:absolute;inset:-1px;
  border-radius:28px;
  background:var(--bg);
}
.l-logo-inner{
  position:relative;z-index:1;
  width:120px;height:120px;border-radius:26px;
  background:var(--white);
  border:1px solid var(--border);
  box-shadow:0 8px 40px rgba(165,53,53,0.16), 0 3px 10px rgba(0,0,0,0.08);
  display:flex;align-items:center;justify-content:center;
  overflow:hidden;
}
.l-logo-inner img,.l-logo-inner .ghost-logo{
  width:86px;height:86px;object-fit:contain;
  filter:drop-shadow(0 2px 12px rgba(165,53,53,0.3));
}
@keyframes spinRing{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}

/* TITLE — bigger + subtle black shadow */
.l-site-name{
  font-family:'Syne',sans-serif;
  font-size:36px;
  font-weight:800;
  letter-spacing:.06em;
  color:var(--crimson);
  text-align:center;
  text-shadow:
    0 1px 3px rgba(0,0,0,0.18),
    0 2px 8px rgba(0,0,0,0.09);
}

/* ── CARDS ── */
.l-cards{
  display:flex;gap:20px;width:100%;max-width:min(780px,88vw);align-items:stretch;
  position:relative;z-index:2;
}

.l-cards .panel.panel-default{
  flex:1!important;
  background:var(--surface)!important;
  border:1px solid var(--border)!important;
  border-radius:var(--radius)!important;
  display:flex!important;flex-direction:column!important;
  overflow:hidden;margin:0!important;
  box-shadow:0 2px 16px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04)!important;
  opacity:0;
  transition:border-color .3s, box-shadow .35s, transform .35s cubic-bezier(.16,1,.3,1);
}
.l-cards .panel.panel-default:hover{
  border-color:var(--border-h)!important;
  transform:translateY(-4px);
  box-shadow:0 20px 50px rgba(165,53,53,0.1), 0 6px 20px rgba(0,0,0,0.07)!important;
}
#register-form{animation:slideFromLeft 1.1s cubic-bezier(.16,1,.3,1) .15s forwards;}
#login-form{animation:slideFromRight 1.1s cubic-bezier(.16,1,.3,1) .25s forwards;}

/* Sliding crimson top bar */
.l-cards .panel.panel-default .panel-heading{position:relative;overflow:hidden;}
.l-cards .panel.panel-default .panel-heading::after{
  content:'';position:absolute;
  top:0;left:0;right:0;height:3px;
  background:linear-gradient(90deg, var(--crimson-d), var(--crimson), var(--crimson-h));
  transform:translateX(-101%);
  transition:transform .45s cubic-bezier(.16,1,.3,1);
}
.l-cards .panel.panel-default:hover .panel-heading::after{transform:translateX(0);}

/* ── PANEL HEADING ── */
.l-cards .panel.panel-default .panel-heading{
  padding:22px 24px 18px!important;
  border-bottom:1px solid var(--border)!important;
  background:transparent!important;border-radius:0!important;flex-shrink:0;
}
.l-ph-title{font-family:'Syne',sans-serif;font-size:17px;font-weight:700;color:var(--text);letter-spacing:.01em;}
.l-ph-sub{font-size:11px;font-weight:400;color:var(--text2);margin-top:4px;line-height:1.5;}

/* ── PANEL BODY ── */
.l-cards .panel.panel-default .panel-body{
  padding:24px 24px 30px!important;
  background:transparent!important;
  display:flex!important;flex-direction:column!important;flex:1!important;
}

/* ── LABELS ── */
.l-lbl{display:block;font-size:10.5px;font-weight:600;color:var(--text2);margin-bottom:7px;letter-spacing:.1em;text-transform:uppercase;}

/* ── INTRO ── */
.l-intro{font-size:12.5px;color:var(--text3);line-height:1.65;margin-bottom:18px;}

/* ── INPUTS ── */
.l-cards .form-control{
  width:100%!important;height:50px!important;padding:0 13px!important;
  background:var(--input-bg)!important;
  border:1.5px solid var(--border)!important;
  border-radius:var(--radius-sm)!important;
  font-family:'DM Sans',sans-serif!important;
  font-size:14px!important;font-weight:400!important;
  color:var(--text)!important;
  outline:none!important;box-shadow:none!important;-webkit-appearance:none;
  transition:border-color .2s,background .2s,box-shadow .2s;
}
.l-cards .form-control::placeholder{color:var(--text3)!important;font-size:13px!important;}
.l-cards .form-control:focus{
  border-color:var(--crimson)!important;
  background:var(--input-focus)!important;
  box-shadow:0 0 0 3.5px var(--glow)!important;
}
.l-cards .form-group{margin-bottom:16px!important;}

/* ── CHECKBOX ── */
.l-cards .checkbox,.l-cards .checkbox label{color:var(--text2)!important;font-size:12.5px!important;font-weight:400!important;}
.l-cards .checkbox{margin:6px 0 16px!important;}
.l-cards .checkbox input[type=checkbox]{
  width:16px;height:16px;border-radius:5px;
  border:1.5px solid var(--border);background:var(--input-bg);
  appearance:none;-webkit-appearance:none;cursor:pointer;
  position:relative;flex-shrink:0;transition:all .15s;vertical-align:middle;margin-right:8px;
}
.l-cards .checkbox input[type=checkbox]:checked{background:var(--crimson);border-color:var(--crimson);box-shadow:0 0 0 3px var(--glow);}
.l-cards .checkbox input[type=checkbox]:checked::after{content:'';position:absolute;left:4px;top:2px;width:6px;height:9px;border:2px solid white;border-top:none;border-left:none;transform:rotate(45deg);}

/* ── BUTTON ── */
.l-cards .btn.btn-primary,
.l-cards .btn.btn-large.btn-primary{
  width:100%!important;height:52px!important;
  background:var(--crimson)!important;color:#fff!important;
  border:none!important;border-radius:var(--radius-sm)!important;
  font-family:'Syne',sans-serif!important;
  font-size:12px!important;font-weight:700!important;
  letter-spacing:.14em!important;text-transform:uppercase!important;
  cursor:pointer;text-shadow:none!important;
  display:flex!important;align-items:center!important;justify-content:center!important;
  position:relative;overflow:hidden;
  transition:background .2s, transform .2s, box-shadow .2s!important;
}
.l-cards .btn.btn-primary::before,
.l-cards .btn.btn-large.btn-primary::before{
  content:'';position:absolute;top:0;left:-100%;width:60%;height:100%;
  background:linear-gradient(90deg, transparent, rgba(255,255,255,0.18), transparent);
  transform:skewX(-20deg);transition:left .4s ease;
}
.l-cards .btn.btn-primary:hover::before,
.l-cards .btn.btn-large.btn-primary:hover::before{left:150%;}
.l-cards .btn.btn-primary:hover,
.l-cards .btn.btn-large.btn-primary:hover{
  background:var(--crimson-h)!important;
  transform:translateY(-2px)!important;
  box-shadow:0 10px 28px var(--glow), 0 3px 8px rgba(0,0,0,0.1)!important;
}
.l-cards .btn.btn-primary:active,
.l-cards .btn.btn-large.btn-primary:active{transform:translateY(0)!important;}

/* ── FORGOT ── */
.l-forgot{text-align:right;margin-top:10px;}
.l-cards .link-accent{font-size:12px!important;color:var(--text3)!important;text-decoration:none!important;font-weight:400!important;transition:color .15s;}
.l-cards .link-accent:hover{color:var(--crimson)!important;}

/* ── COL OVERRIDES ── */
.l-cards .row{display:block!important;}
.l-cards .col-lg-4,.l-cards .col-lg-8{width:100%!important;float:none!important;padding:0!important;}
.l-cards .col-lg-8{margin-top:8px!important;}

/* ── SPACER ── */
.l-spacer{flex:1;min-height:4px;}

/* ── ALERT ── */
.l-cards .alert-danger{
  padding:11px 14px!important;
  background:rgba(165,53,53,0.06)!important;
  border:1px solid rgba(165,53,53,0.2)!important;
  border-radius:var(--radius-sm)!important;
  font-size:12px!important;color:var(--crimson-d)!important;
  margin-bottom:16px!important;line-height:1.5;
}

/* ── BADGE ── */
.l-badge{
  display:inline-flex;align-items:center;gap:6px;
  padding:5px 11px;border-radius:20px;
  background:rgba(165,53,53,0.07);border:1px solid rgba(165,53,53,0.09);
  font-size:9.5px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;
  color:var(--crimson);margin-bottom:16px;
}
.l-badge::before{content:'';width:5px;height:5px;border-radius:50%;background:var(--crimson);animation:badgePulse 2.5s ease infinite;}
@keyframes badgePulse{0%,100%{opacity:1;transform:scale(1);}50%{opacity:.4;transform:scale(1.5);}}

/* ── FOOTER ── */
.l-footer{
  margin-top:20px;
  display:flex;align-items:center;justify-content:center;gap:18px;
  font-size:11px;color:var(--text3);
  opacity:0;animation:fadeUp .5s ease .5s forwards;
  position:relative;z-index:2;
}
.l-footer select{
  background:var(--white)!important;border:1px solid var(--border)!important;
  border-radius:7px!important;color:var(--text2)!important;
  font-family:'DM Sans',sans-serif!important;font-size:12px!important;
  padding:5px 10px!important;outline:none!important;cursor:pointer!important;
}
.l-footer-dot{width:3px;height:3px;border-radius:50%;background:var(--border);}

/* ── ANIMATIONS ── */
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
@keyframes slideFromLeft{from{opacity:0;transform:translateX(-60px) scale(0.97)}to{opacity:1;transform:translateX(0) scale(1)}}
@keyframes slideFromRight{from{opacity:0;transform:translateX(60px) scale(0.97)}to{opacity:1;transform:translateX(0) scale(1)}}
.shake{animation:lshake .45s ease!important;}
@keyframes lshake{0%,100%{transform:translateX(0)}20%{transform:translateX(-7px)}60%{transform:translateX(7px)}80%{transform:translateX(-3px)}}

/* ── RESPONSIVE ── */
@media(max-width:660px){
  .l-cards{flex-direction:column!important;max-width:min(420px,92vw)!important;}
  #user-auth-login{padding:32px 16px!important;}
  .l-deco-arc-tl,.l-deco-arc-br,.l-deco-diamond,.l-deco-cross,.l-deco-line-left{display:none;}
  .l-bg-logo{width:300px;height:300px;}
  .l-site-name{font-size:28px;}
}
CSS);
?>

<!-- background orbs -->
<div id="l-bg">
  <div class="l-orb l-orb-1"></div>
  <div class="l-orb l-orb-2"></div>
  <div class="l-orb l-orb-3"></div>
  <div class="l-orb l-orb-4"></div>
  <div class="l-bg-dots"></div>
  <div class="l-bg-streak"></div>
</div>

<!-- edge lines -->
<div class="l-deco-line-top"></div>
<div class="l-deco-line-left"></div>

<!-- corner arcs -->
<div class="l-deco-arc-tl"></div>
<div class="l-deco-arc-br"></div>

<!-- floating diamonds -->
<div class="l-deco-diamond d1"></div>
<div class="l-deco-diamond d2"></div>
<div class="l-deco-diamond d3"></div>
<div class="l-deco-diamond d4"></div>

<!-- cross marks -->
<div class="l-deco-cross c1">✕</div>
<div class="l-deco-cross c2">✕</div>
<div class="l-deco-cross c3">✕</div>

<!-- extra red shapes -->
<div class="l-deco-qc-tr"></div>
<div class="l-deco-hline-mid"></div>
<div class="l-deco-vline-right"></div>
<div class="l-deco-slash"></div>
<div class="l-deco-dot dot1"></div>
<div class="l-deco-dot dot2"></div>
<div class="l-deco-dot dot3"></div>
<div class="l-deco-dot dot4"></div>
<div class="l-deco-dot dot5"></div>
<div class="l-deco-bracket"></div>
<div class="l-deco-bracket-bl"></div>

<div id="user-auth-login">

  <!-- BG LOGO — inside the login container so it stays near the forms -->
  <img class="l-bg-logo" src="/humhub/themes/theme2/views/user/img/logo.png" alt="">

  <!-- HEADER -->
  <div class="l-header">
    <div class="l-logo-wrap">
      <div class="l-logo-inner">
        <img src="/humhub/themes/theme2/views/user/img/logo.png" alt="CITOYENS CSEFRS" class="ghost-logo">
      </div>
    </div>
    <div class="l-site-name">CITOYENS CSEFRS</div>
  </div>

  <div class="l-cards">

    <!-- ── SIGN UP ── -->
    <?php if ($canRegister && $showRegistrationForm): ?>
    <div class="panel panel-default" id="register-form">
      <div class="panel-heading">
        <div class="l-ph-title"><?= Yii::t('UserModule.auth', 'Create Account') ?></div>
        <div class="l-ph-sub"><?= Yii::t('UserModule.auth', 'Join the network in seconds') ?></div>
      </div>
      <div class="panel-body">
        <?php if (AuthChoice::hasClients()): ?>
          <?= AuthChoice::widget(['showOrDivider' => true]) ?>
        <?php else: ?>
          <p class="l-intro"><?= Yii::t('UserModule.auth', "Don't have an account? Join the network by entering your e-mail address.") ?></p>
        <?php endif; ?>

        <?php $form = ActiveForm::begin(['id' => 'invite-form']); ?>
        <label class="l-lbl"><?= Yii::t('UserModule.auth', 'Email') ?></label>
        <?= $form->field($invite, 'email')->input('email', ['id' => 'register-email', 'placeholder' => $invite->getAttributeLabel('email'), 'aria-label' => $invite->getAttributeLabel('email')])->label(false) ?>
        <?php if ($invite->showCaptureInRegisterForm()): ?>
          <div id="registration-form-captcha" style="display:none;">
            <?= $form->field($invite, 'captcha')->widget(CaptchaField::class)->label(false) ?>
          </div>
        <?php endif; ?>
        <?= Html::submitButton(Yii::t('UserModule.auth', 'Register'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>
        <?php ActiveForm::end(); ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- ── LOGIN ── -->
    <div class="panel panel-default" id="login-form">
      <div class="panel-heading">
        <div class="l-ph-title"><?= Yii::t('UserModule.auth', 'Accéder à votre compte') ?></div>
        <div class="l-ph-sub">
          <?= $canRegister
            ? Yii::t('UserModule.auth', "If you're already a member, please login with your username/email and password.")
            : Yii::t('UserModule.auth', "Please login with your username/email and password.") ?>
        </div>
      </div>
      <div class="panel-body">
        <?php if (Yii::$app->session->hasFlash('error')): ?>
          <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
        <?php endif; ?>

        <?php if (AuthChoice::hasClients()): ?>
          <?= AuthChoice::widget(['showOrDivider' => $showLoginForm]) ?>
        <?php endif; ?>

        <?php if ($showLoginForm): ?>
          <?php $form = ActiveForm::begin(['id' => 'account-login-form', 'enableClientValidation' => false]) ?>
          <label class="l-lbl"><?= Yii::t('UserModule.auth', 'Username or Email') ?></label>
          <?= $form->field($model, 'username')->textInput(['id' => 'login_username', 'placeholder' => $model->getAttributeLabel('username'), 'aria-label' => $model->getAttributeLabel('username')])->label(false) ?>
          <label class="l-lbl"><?= Yii::t('UserModule.auth', 'Password') ?></label>
          <?= $form->field($model, 'password')->passwordInput(['id' => 'login_password', 'placeholder' => $model->getAttributeLabel('password'), 'aria-label' => $model->getAttributeLabel('password')])->label(false) ?>
          <?= $model->hideRememberMe ? '' : $form->field($model, 'rememberMe')->checkbox() ?>
          <?= Html::submitButton(Yii::t('UserModule.auth', 'Sign in'), ['id' => 'login-button', 'data-ui-loader' => '', 'class' => 'btn btn-large btn-primary']) ?>
          <?php if ($passwordRecoveryRoute): ?>
            <div class="l-forgot">
              <?= Html::a(
                Yii::t('UserModule.auth', 'Forgot your password?'),
                $passwordRecoveryRoute,
                ['id' => 'password-recovery-link', 'class' => 'link-accent', 'target' => is_array($passwordRecoveryRoute) ? '_self' : '_blank', 'data' => ['pjax-prevent' => true]]
              ) ?>
            </div>
          <?php endif; ?>
          <?php ActiveForm::end(); ?>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- FOOTER -->
  <div class="l-footer">
    <span>© <?= date('Y') ?> CITOYENS CSEFRS</span>
    <div class="l-footer-dot"></div>
    <span><?= Yii::t('UserModule.auth', 'All rights reserved') ?></span>
    <div class="l-footer-dot"></div>
    <div><?= LanguageChooser::widget(['vertical' => false, 'hideLabel' => true]) ?></div>
  </div>

</div>

<script <?= Html::nonce() ?>>
$(function(){
  $('#login_username').focus();
  <?php if($model->hasErrors()){?>$('#login-form').addClass('shake');<?php }?>
  <?php if($invite->hasErrors()){?>$('#register-form').addClass('shake');<?php }?>
  <?php if($invite->showCaptureInRegisterForm()){?>$('#register-email').on('focus',function(){$('#registration-form-captcha').fadeIn(300);});<?php }?>
});
</script>