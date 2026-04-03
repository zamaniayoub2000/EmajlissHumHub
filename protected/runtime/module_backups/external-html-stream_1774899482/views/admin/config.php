<?php

use humhub\modules\externalHtmlStream\models\ConfigForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * @var \humhub\modules\ui\view\components\View $this
 * @var ConfigForm $model
 * @var \humhub\modules\space\models\Space[] $spaces
 */

$this->title = Yii::t('ExternalHtmlStreamModule.base', 'Configuration du module');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <i class="fa fa-cog"></i>
            Configuration — Majliss Sync & External HTML Stream
        </h4>
    </div>

    <div class="panel-body">
        <?php $form = ActiveForm::begin(['id' => 'config-form']); ?>

        <!-- ═══ API REST WordPress ═══ -->
        <h5 style="border-bottom: 2px solid #667eea; padding-bottom: 8px; margin-top: 0; color: #667eea;">
            <i class="fa fa-wordpress"></i> API REST WordPress Majliss
        </h5>

        <div class="alert alert-info" style="font-size: 13px;">
            <i class="fa fa-info-circle"></i>
            <strong>API REST WordPress :</strong> Le module se connecte via l'API REST de WordPress
            (<code>/wp-json/wp/v2/posts</code>) — aucun accès direct à la base de données n'est nécessaire.
            <br>
            Si l'API est publique (par défaut dans WP), laissez l'authentification sur "Aucune".
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'wpApiBaseUrl')->textInput([
                    'placeholder' => 'https://majliscom.csefrs.ma',
                ])->hint('URL du site WordPress (sans /wp-json). Ex: https://majliscom.csefrs.ma') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'wpApiEndpoint')->textInput([
                    'placeholder' => '/wp-json/wp/v2',
                ])->hint('Endpoint REST par défaut. Ne changez que si endpoint personnalisé.') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'wpAuthMethod')->dropDownList([
                    'none'                 => 'Aucune (API publique)',
                    'basic'                => 'Basic Auth (user:password)',
                    'application_password' => 'Application Password (WP 5.6+)',
                    'jwt'                  => 'JWT Token',
                ]) ?>
            </div>
            <div class="col-md-4" id="auth-user-field">
                <?= $form->field($model, 'wpAuthUser')->textInput([
                    'placeholder' => 'admin',
                ]) ?>
            </div>
            <div class="col-md-4" id="auth-pass-field">
                <?= $form->field($model, 'wpAuthPassword')->passwordInput([
                    'placeholder' => '••••••••',
                ])->hint('Application Password : Utilisateurs > Profil > Mots de passe d\'application') ?>
            </div>
        </div>

        <div class="row" id="jwt-token-row" style="display: none;">
            <div class="col-md-12">
                <?= $form->field($model, 'wpJwtToken')->textInput([
                    'placeholder' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'wpCategoryFilter')->textInput([
                    'placeholder' => '3,7,12 (vide = toutes)',
                ])->hint('IDs des catégories WordPress à synchroniser. Vide = toutes les catégories.') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'imageBaseUrl')->textInput([
                    'placeholder' => 'https://intranet.csefrs.ma',
                ])->hint('URL pour réécrire les chemins d\'images si nécessaire.') ?>
            </div>
        </div>

        <!-- Bouton test API -->
        <div style="margin: 15px 0; padding: 12px; background: #f8f9fa; border-radius: 6px; border: 1px dashed #dee2e6;">
            <button type="button" class="btn btn-info" id="btn-test-wp-api">
                <i class="fa fa-plug"></i> Tester la connexion API WordPress
            </button>
            <span id="wp-api-test-result" style="margin-left: 15px;"></span>
        </div>

        <!-- ═══ Synchronisation ═══ -->
        <h5 style="border-bottom: 2px solid #28a745; padding-bottom: 8px; margin-top: 25px; color: #28a745;">
            <i class="fa fa-exchange"></i> Paramètres de synchronisation
        </h5>

        <div class="row">
            <div class="col-md-4">
                <?= $form->field($model, 'targetSpaceId')->dropDownList(
                    ArrayHelper::map($spaces, 'id', 'name'),
                    ['prompt' => '— Sélectionner un espace —']
                ) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'batchLimit')->textInput([
                    'type' => 'number', 'min' => 1, 'max' => 100,
                ])->hint('Correspond au paramètre per_page de l\'API WP (max 100).') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'imageDownloadTimeout')->textInput([
                    'type' => 'number', 'min' => 5, 'max' => 60,
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'autoSyncEnabled')->checkbox() ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'fallbackImage')->textInput([
                    'placeholder' => 'https://example.com/default-image.jpg',
                ]) ?>
            </div>
        </div>

        <!-- ═══ Paramètres généraux ═══ -->
        <h5 style="border-bottom: 2px solid #17a2b8; padding-bottom: 8px; margin-top: 25px; color: #17a2b8;">
            <i class="fa fa-sliders"></i> Paramètres généraux
        </h5>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'enableCache')->checkbox() ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'allowIframes')->checkbox() ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'apiTimeout')->textInput(['type' => 'number', 'min' => 5, 'max' => 120]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'whitelistedDomains')->textInput([
                    'placeholder' => 'api.exemple.com, cdn.exemple.com',
                ]) ?>
            </div>
        </div>

        <hr>

        <div class="form-group">
            <?= Button::save()->submit() ?>
            <?= Button::defaultType('Retour au tableau de bord')->link(['index']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$testConnectionUrl = Url::to(['test-connection']);
$js = <<<JS
// ── Afficher/masquer les champs d'auth selon la méthode ──
function toggleAuthFields() {
    var method = $('#configform-wpauthmethod').val();
    var showBasic = (method === 'basic' || method === 'application_password');
    var showJwt   = (method === 'jwt');

    $('#auth-user-field').toggle(showBasic);
    $('#auth-pass-field').toggle(showBasic || showJwt);
    $('#jwt-token-row').toggle(showJwt);
}

$('#configform-wpauthmethod').on('change', toggleAuthFields);
toggleAuthFields();

// ── Test connexion API WordPress ──
$('#btn-test-wp-api').on('click', function() {
    var btn = $(this);
    var resultSpan = $('#wp-api-test-result');

    btn.prop('disabled', true);
    btn.find('.fa').removeClass('fa-plug').addClass('fa-spinner fa-spin');
    resultSpan.html('<span class="text-muted">Test en cours...</span>');

    $.ajax({
        url: '{$testConnectionUrl}',
        type: 'GET',
        dataType: 'json',
        timeout: 30000,
        success: function(data) {
            if (data.success) {
                resultSpan.html(
                    '<span class="text-success"><i class="fa fa-check-circle"></i> ' + data.message + '</span>' +
                    (data.remaining > 0
                        ? ' <span class="label label-warning">' + data.remaining + ' restant(s)</span>'
                        : ' <span class="label label-success">À jour</span>'
                    )
                );
            } else {
                resultSpan.html(
                    '<span class="text-danger"><i class="fa fa-times-circle"></i> ' + data.message + '</span>'
                );
            }
        },
        error: function(xhr) {
            resultSpan.html(
                '<span class="text-danger"><i class="fa fa-times-circle"></i> Erreur de communication avec le serveur.</span>'
            );
        },
        complete: function() {
            btn.prop('disabled', false);
            btn.find('.fa').removeClass('fa-spinner fa-spin').addClass('fa-plug');
        }
    });
});
JS;
$this->registerJs($js);
?>
