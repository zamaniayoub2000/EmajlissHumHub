<?php

use humhub\helpers\Html;
use humhub\modules\user\models\forms\Login;
use humhub\modules\user\models\Invite;
use humhub\modules\user\widgets\AuthChoice;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\LanguageChooser;
use humhub\widgets\SiteLogo;

$this->pageTitle = Yii::t('UserModule.auth', 'Login');

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
#user-auth-login ~ *,
.powered-by, [class*="powered"],
footer .humhub-info, .humhub-powered,
footer span, footer a[href*="humhub"], footer {
    display: none !important;
}

/* ── Page centering ── */
#user-auth-login {
    font-family: 'Ubuntu', sans-serif;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 100vh;
    padding: 32px 16px;
    gap: 0;
}

/* ── Logo bar — sits above card, no border ── */
.login-logo-bar {
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

.login-logo-bar img.logo-left {
    height: 52px;
    width: auto;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));
}

.login-logo-bar .logo-divider {
    width: 1px;
    height: 40px;
    background: var(--bd);
    flex-shrink: 0;
}

.login-logo-bar img.logo-right {
    height: 58px;
    width: auto;
    opacity: 0.88;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));
}

/* ── Card ── */
#user-auth-login .panel.panel-default {
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

/* ── Remove heading bar entirely ── */
#user-auth-login .panel-heading {
    display: none !important;
}

/* ── Body ── */
#user-auth-login .panel-body {
    font-family: 'Ubuntu', sans-serif !important;
    padding: 28px 28px 32px !important;
    background: #fff !important;
}

#user-auth-login .panel-body p {
    font-size: 13px !important;
    color: var(--tx2) !important;
    margin-bottom: 16px !important;
    line-height: 1.55 !important;
}

/* ── Inputs ── */
#user-auth-login .form-control {
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

#user-auth-login .form-control::placeholder {
    color: var(--tx3) !important;
    font-size: 13px !important;
}

#user-auth-login .form-control:focus {
    border-color: var(--cr) !important;
    background: var(--ifocus) !important;
    box-shadow: 0 0 0 3px var(--glow) !important;
    outline: none !important;
}

#user-auth-login .form-group {
    margin-bottom: 12px !important;
}

/* ── Checkbox ── */
#user-auth-login .checkbox { margin: 4px 0 6px !important; }

#user-auth-login .checkbox label {
    font-family: 'Ubuntu', sans-serif !important;
    font-size: 13px !important;
    color: var(--tx2) !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    cursor: pointer !important;
    margin: 0 !important;
}

#user-auth-login .checkbox input[type="checkbox"] {
    width: 16px !important; height: 16px !important;
    border-radius: 4px !important;
    border: 1.5px solid var(--bd) !important;
    background: var(--ibg) !important;
    appearance: none !important; -webkit-appearance: none !important;
    cursor: pointer !important; flex-shrink: 0 !important;
    position: relative !important; margin: 0 !important;
    transition: all 0.15s !important;
}

#user-auth-login .checkbox input[type="checkbox"]:checked {
    background: var(--cr) !important;
    border-color: var(--cr) !important;
    box-shadow: 0 0 0 3px var(--glow) !important;
}

#user-auth-login .checkbox input[type="checkbox"]:checked::after {
    content: '';
    position: absolute;
    left: 4px; top: 1px;
    width: 6px; height: 9px;
    border: 2px solid #fff;
    border-top: none; border-left: none;
    transform: rotate(45deg);
}

/* ── HR ── */
#user-auth-login hr {
    border-color: var(--bd) !important;
    margin: 16px 0 !important;
}

/* ── Row layout ── */
#user-auth-login .row {
    display: flex !important;
    align-items: center !important;
    margin: 0 !important;
}

#user-auth-login .col-lg-4 {
    flex: 0 0 auto !important;
    width: auto !important;
    padding: 0 !important;
}

#user-auth-login .col-lg-8 {
    flex: 1 1 auto !important;
    padding: 0 !important;
    text-align: right !important;
}

/* ── Sign in button ── */
#user-auth-login #login-button,
#user-auth-login .btn.btn-primary {
    font-family: 'Ubuntu', sans-serif !important;
    height: 44px !important;
    padding: 0 28px !important;
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

#user-auth-login #login-button:hover,
#user-auth-login .btn.btn-primary:hover {
    background: var(--cr-h) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 22px rgba(165,53,53,0.30) !important;
}

#user-auth-login #login-button:active,
#user-auth-login .btn.btn-primary:active {
    transform: translateY(0) !important;
}

/* ── Forgot password ── */
#user-auth-login #password-recovery-link,
#user-auth-login .link-accent {
    font-family: 'Ubuntu', sans-serif !important;
    font-size: 12px !important;
    color: var(--tx3) !important;
    text-decoration: none !important;
    transition: color 0.15s !important;
}

#user-auth-login #password-recovery-link:hover,
#user-auth-login .link-accent:hover {
    color: var(--cr) !important;
}

/* ── Error alert ── */
#user-auth-login .alert-danger {
    font-family: 'Ubuntu', sans-serif !important;
    padding: 10px 14px !important;
    border-radius: var(--rs) !important;
    background: rgba(165,53,53,0.06) !important;
    border: 1px solid rgba(165,53,53,0.2) !important;
    color: var(--cr-d) !important;
    font-size: 12.5px !important;
    margin-bottom: 14px !important;
}

/* ── Language chooser ── */
#user-auth-login label,
#user-auth-login * label {
    color: var(--tx2) !important;
    font-family: 'Ubuntu', sans-serif !important;
    font-size: 13px !important;
}

#user-auth-login .language-chooser,
#user-auth-login > form,
#user-auth-login > div:not(.panel):not(#login-form):not(.login-logo-bar) {
    width: 200px !important;
    max-width: 200px !important;
}

#user-auth-login select {
    width: 100% !important;
    height: 38px !important;
    padding: 0 36px 0 14px !important;
    border: 1px solid var(--bd) !important;
    border-radius: 8px !important;
    background-color: var(--ibg) !important;
    font-family: 'Ubuntu', sans-serif !important;
    font-size: 13px !important;
    color: var(--tx) !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    outline: none !important;
    cursor: pointer !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23a8a098' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 12px center !important;
}

@media (max-width: 440px) {
    #user-auth-login .panel.panel-default,
    .login-logo-bar { width: calc(100vw - 32px) !important; }
}
</style>

<div id="user-auth-login" class="container">

    <!-- Logo bar above card -->
    <div class="login-logo-bar">
      <img class="logo-left" src="/themes/HumHub/img/emajlis.png" alt="eMajlis">
      <div class="logo-divider"></div>
      <img class="logo-right" src="/themes/HumHub/img/pic.png" alt="CSE Logo">
    </div>

    <div class="panel panel-default" id="login-form">
        <div class="panel-body">

            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="alert alert-danger" role="alert">
                    <?= Yii::$app->session->getFlash('error') ?>
                </div>
            <?php endif; ?>

            <?php if (AuthChoice::hasClients()): ?>
                <?= AuthChoice::widget(['showOrDivider' => $showLoginForm]) ?>
            <?php else: ?>
                <?php if ($canRegister) : ?>
                    <p><?= Yii::t('UserModule.auth', "If you're already a member, please login with your username/email and password.") ?></p>
                <?php elseif ($showLoginForm): ?>
                    <p><?= Yii::t('UserModule.auth', "Please login with your username/email and password.") ?></p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($showLoginForm): ?>
                <?php $form = ActiveForm::begin(['id' => 'account-login-form', 'enableClientValidation' => false]) ?>
                    <?= $form->field($model, 'username')->textInput(['id' => 'login_username', 'placeholder' => $model->getAttributeLabel('username'), 'aria-label' => $model->getAttributeLabel('username')])->label(false) ?>
                    <?= $form->field($model, 'password')->passwordInput(['id' => 'login_password', 'placeholder' => $model->getAttributeLabel('password'), 'aria-label' => $model->getAttributeLabel('password')])->label(false) ?>
                    <?= $model->hideRememberMe ? '' : $form->field($model, 'rememberMe')->checkbox() ?>
                    <hr>
                    <div class="row">
                        <div class="col-lg-4">
                            <?= Html::submitButton(Yii::t('UserModule.auth', 'Sign in'), ['id' => 'login-button', 'data-ui-loader' => "", 'class' => 'btn btn-large btn-primary']); ?>
                        </div>
                        <?php if ($passwordRecoveryRoute) : ?>
                            <div class="col-lg-8 text-end">
                                <small>
                                    <?= Html::a(
                                        Html::tag('br') . Yii::t('UserModule.auth', 'Forgot your password?'),
                                        $passwordRecoveryRoute,
                                        ['id' => 'password-recovery-link', 'class' => 'link-accent', 'target' => is_array($passwordRecoveryRoute) ? '_self' : '_blank', 'data' => ['pjax-prevent' => true]]
                                    ) ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php ActiveForm::end(); ?>
            <?php endif; ?>
        </div>
    </div>

    <?= LanguageChooser::widget(['vertical' => true]) ?>
</div>

<script <?= Html::nonce() ?>>
    $(function () { $('#login_username').focus(); });
    <?php if ($model->hasErrors()) { ?>
    $('#login-form').addClass('shake');
    <?php } ?>
</script>