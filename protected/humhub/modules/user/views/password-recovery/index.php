<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\AccountRecoverPassword;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\CaptchaField;
use humhub\widgets\SiteLogo;
use yii\helpers\Url;

$this->pageTitle = Yii::t('UserModule.auth', 'Password recovery');

/**
 * @var $model AccountRecoverPassword
 */

?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap');

:root {
    --cr: #A53535;
    --cr-h: #c04040;
    --cr-d: #8b2c2c;
    --glow: rgba(165,53,53,0.13);
    --bd: #e8e3dc;
    --tx: #1c1917;
    --tx2: #6b6560;
    --tx3: #a8a098;
    --ibg: #faf9f7;
    --ifocus: #fff8f8;
    --r: 16px;
    --rs: 10px;
}

*, *::before, *::after { box-sizing: border-box; }

body,
#wrapper, #layout-content, .content-container,
.layout-nav-main-container, .container, .container-fluid,
main, #page-wrapper, .page-wrapper, .layout-content-container {
    background: #f4f1ee !important;
    font-family: 'Ubuntu', sans-serif !important;
}

/* ── Hide "Powered by HumHub" ── */
#user-auth-recovery ~ *,
.powered-by, [class*="powered"],
footer .humhub-info, .humhub-powered,
footer span, footer a[href*="humhub"], footer {
    display: none !important;
}

/* ── Page centering ── */
#user-auth-recovery {
    font-family: 'Ubuntu', sans-serif;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 100vh;
    padding: 32px 16px;
    gap: 0;
}

/* ── Logo bar ── */
.recovery-logo-bar {
    width: 400px;
    max-width: calc(100vw - 32px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 28px 18px;
    background: #fff;
    border-radius: var(--r) var(--r) 0 0;
    border: 1px solid var(--bd);
    border-bottom: 1px solid #f0ece6;
}

.recovery-logo-bar img.logo-left {
    height: 52px;
    width: auto;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));
}

.recovery-logo-bar .logo-divider {
    width: 1px;
    height: 40px;
    background: var(--bd);
    flex-shrink: 0;
}

.recovery-logo-bar img.logo-right {
    height: 58px;
    width: auto;
    opacity: 0.88;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));
}

/* ── Card ── */
#user-auth-recovery .panel.panel-default {
    font-family: 'Ubuntu', sans-serif;
    width: 400px !important;
    max-width: calc(100vw - 32px) !important;
    float: none !important;
    margin: 0 0 14px 0 !important;
    border: 1px solid var(--bd) !important;
    border-top: none !important;
    border-radius: 0 0 var(--r) var(--r) !important;
    box-shadow: 0 8px 32px rgba(0,0,0,0.10) !important;
    overflow: hidden !important;
    background: #fff !important;
}

/* ── Remove heading bar ── */
#user-auth-recovery .panel-heading {
    display: none !important;
}

/* ── Body ── */
#user-auth-recovery .panel-body {
    font-family: 'Ubuntu', sans-serif !important;
    padding: 28px 28px 32px !important;
    background: #fff !important;
}

#user-auth-recovery .panel-body p {
    font-size: 13px !important;
    color: var(--tx2) !important;
    margin-bottom: 16px !important;
    line-height: 1.55 !important;
}

/* ── Inputs ── */
#user-auth-recovery .form-control {
    font-family: 'Ubuntu', sans-serif !important;
    height: 46px !important;
    padding: 0 14px !important;
    border: 1.5px solid var(--bd) !important;
    border-radius: var(--rs) !important;
    background: var(--ibg) !important;
    color: var(--tx) !important;
    font-size: 14px !important;
    box-shadow: none !important;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s !important;
    width: 100% !important;
}

#user-auth-recovery .form-control::placeholder {
    color: var(--tx3) !important;
    font-size: 13px !important;
}

#user-auth-recovery .form-control:focus {
    border-color: var(--cr) !important;
    background: var(--ifocus) !important;
    box-shadow: 0 0 0 3px var(--glow) !important;
    outline: none !important;
}

#user-auth-recovery .form-group {
    margin-bottom: 12px !important;
}

/* ── HR ── */
#user-auth-recovery hr {
    border-color: var(--bd) !important;
    margin: 16px 0 !important;
}

/* ── Buttons row ── */
#user-auth-recovery .btn-row {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

/* ── Back button ── */
#user-auth-recovery .btn.btn-light,
#user-auth-recovery .btn-light {
    font-family: 'Ubuntu', sans-serif !important;
    height: 44px !important;
    padding: 0 20px !important;
    background: #f4f1ee !important;
    color: var(--tx2) !important;
    border: 1.5px solid var(--bd) !important;
    border-radius: var(--rs) !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    letter-spacing: 0.08em !important;
    text-transform: uppercase !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: background 0.2s, transform 0.2s !important;
    text-decoration: none !important;
}

#user-auth-recovery .btn.btn-light:hover,
#user-auth-recovery .btn-light:hover {
    background: var(--bd) !important;
    transform: translateY(-2px) !important;
}

/* ── Reset password button ── */
#user-auth-recovery .btn.btn-primary {
    font-family: 'Ubuntu', sans-serif !important;
    height: 44px !important;
    padding: 0 20px !important;
    background: var(--cr) !important;
    color: #fff !important;
    border: none !important;
    border-radius: var(--rs) !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    letter-spacing: 0.11em !important;
    text-transform: uppercase !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: background 0.2s, transform 0.2s, box-shadow 0.2s !important;
}

#user-auth-recovery .btn.btn-primary:hover {
    background: var(--cr-h) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 22px rgba(165,53,53,0.30) !important;
}

#user-auth-recovery .btn.btn-primary:active {
    transform: translateY(0) !important;
}

/* ── Error alert ── */
#user-auth-recovery .alert-danger {
    font-family: 'Ubuntu', sans-serif !important;
    padding: 10px 14px !important;
    border-radius: var(--rs) !important;
    background: rgba(165,53,53,0.06) !important;
    border: 1px solid rgba(165,53,53,0.2) !important;
    color: var(--cr-d) !important;
    font-size: 12.5px !important;
    margin-bottom: 14px !important;
}

@media (max-width: 440px) {
    #user-auth-recovery .panel.panel-default,
    .recovery-logo-bar { width: calc(100vw - 32px) !important; }
}
</style>

<div id="user-auth-recovery" class="container">

    <!-- Logo bar above card -->
    <div class="recovery-logo-bar">
    <img class="logo-left" src="/themes/HumHub/img/emajlis.png" alt="eMajlis">
    <div class="logo-divider"></div>
    <img class="logo-right" src="/themes/HumHub/img/pic.png" alt="CSE Logo">
    </div>

    <div class="panel panel-default animated bounceIn" id="password-recovery-form">
        <div class="panel-body">

            <?php $form = ActiveForm::begin(['enableClientValidation' => false]); ?>

            <p><?= Yii::t('UserModule.auth', 'Just enter your e-mail address. We\'ll send you recovery instructions!'); ?></p>

            <?= $form->field($model, 'email')->textInput(['class' => 'form-control', 'id' => 'email_txt', 'placeholder' => Yii::t('UserModule.auth', 'Your email')])->label(false) ?>

            <div class="mb-3">
                <?= $form->field($model, 'captcha')->widget(CaptchaField::class)->label(false) ?>
            </div>

            <hr>
            <div class="btn-row">
                <?= Button::light(Yii::t('UserModule.auth', 'Back'))->link(Url::home())->pjax(false) ?>
                <?= Html::submitButton(Yii::t('UserModule.auth', 'Reset password'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>

</div>

<script <?= Html::nonce() ?>>
    $(function () {
        $('#email_txt').focus();
    });

    <?php if ($model->hasErrors()) : ?>
    $('#password-recovery-form').removeClass('bounceIn');
    $('#password-recovery-form').addClass('shake');
    $('#app-title').removeClass('fadeIn');
    <?php endif; ?>
</script>