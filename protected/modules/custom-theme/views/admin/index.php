<?php
/** @var \humhub\modules\customTheme\models\CustomThemeForm $model */

use yii\helpers\Url;
use humhub\modules\customTheme\assets\AdminAsset;

AdminAsset::register($this);
$this->title = 'Custom Theme - Dashboard';
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h1><i class="fa fa-paint-brush"></i> Custom Theme Override</h1>
        <p class="text-muted">Personnalisez le thème de votre plateforme HumHub</p>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-3">
                <?= $this->render('_nav', ['active' => 'index']) ?>
            </div>
            <div class="col-md-9">
                <h4><i class="fa fa-toggle-on"></i> État des personnalisations</h4>
                <div class="row">
                    <!-- Footer -->
                    <div class="col-md-6 col-lg-3">
                        <div class="ct-status-card">
                            <div class="ct-status-icon"><i class="fa fa-arrow-down fa-2x"></i></div>
                            <h5>Footer</h5>
                            <div class="ct-toggle-wrapper">
                                <label class="ct-switch">
                                    <input type="checkbox" class="ct-toggle" data-key="footer_active" <?= $model->footer_active ? 'checked' : '' ?>>
                                    <span class="ct-slider"></span>
                                </label>
                                <span class="ct-status-label <?= $model->footer_active ? 'text-success' : 'text-muted' ?>">
                                    <?= $model->footer_active ? 'Actif' : 'Inactif' ?>
                                </span>
                            </div>
                            <a href="<?= Url::to(['/custom-theme/admin/footer']) ?>" class="btn btn-xs btn-primary">
                                <i class="fa fa-edit"></i> Éditer
                            </a>
                        </div>
                    </div>
                    <!-- Header -->
                    <div class="col-md-6 col-lg-3">
                        <div class="ct-status-card">
                            <div class="ct-status-icon"><i class="fa fa-arrow-up fa-2x"></i></div>
                            <h5>Header</h5>
                            <div class="ct-toggle-wrapper">
                                <label class="ct-switch">
                                    <input type="checkbox" class="ct-toggle" data-key="header_active" <?= $model->header_active ? 'checked' : '' ?>>
                                    <span class="ct-slider"></span>
                                </label>
                                <span class="ct-status-label <?= $model->header_active ? 'text-success' : 'text-muted' ?>">
                                    <?= $model->header_active ? 'Actif' : 'Inactif' ?>
                                </span>
                            </div>
                            <a href="<?= Url::to(['/custom-theme/admin/header']) ?>" class="btn btn-xs btn-primary">
                                <i class="fa fa-edit"></i> Éditer
                            </a>
                        </div>
                    </div>
                    <!-- CSS -->
                    <div class="col-md-6 col-lg-3">
                        <div class="ct-status-card">
                            <div class="ct-status-icon"><i class="fa fa-css3 fa-2x"></i></div>
                            <h5>CSS</h5>
                            <div class="ct-toggle-wrapper">
                                <label class="ct-switch">
                                    <input type="checkbox" class="ct-toggle" data-key="css_active" <?= $model->css_active ? 'checked' : '' ?>>
                                    <span class="ct-slider"></span>
                                </label>
                                <span class="ct-status-label <?= $model->css_active ? 'text-success' : 'text-muted' ?>">
                                    <?= $model->css_active ? 'Actif' : 'Inactif' ?>
                                </span>
                            </div>
                            <a href="<?= Url::to(['/custom-theme/admin/css']) ?>" class="btn btn-xs btn-primary">
                                <i class="fa fa-edit"></i> Éditer
                            </a>
                        </div>
                    </div>
                    <!-- JS -->
                    <div class="col-md-6 col-lg-3">
                        <div class="ct-status-card">
                            <div class="ct-status-icon"><i class="fa fa-code fa-2x"></i></div>
                            <h5>JavaScript</h5>
                            <div class="ct-toggle-wrapper">
                                <label class="ct-switch">
                                    <input type="checkbox" class="ct-toggle" data-key="js_active" <?= $model->js_active ? 'checked' : '' ?>>
                                    <span class="ct-slider"></span>
                                </label>
                                <span class="ct-status-label <?= $model->js_active ? 'text-success' : 'text-muted' ?>">
                                    <?= $model->js_active ? 'Actif' : 'Inactif' ?>
                                </span>
                            </div>
                            <a href="<?= Url::to(['/custom-theme/admin/js']) ?>" class="btn btn-xs btn-primary">
                                <i class="fa fa-edit"></i> Éditer
                            </a>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    Les toggles s'appliquent instantanément. Le cache est invalidé automatiquement.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ct-toggle').forEach(function(el) {
        el.addEventListener('change', function() {
            var key = this.getAttribute('data-key');
            var active = this.checked ? 1 : 0;
            var label = this.closest('.ct-toggle-wrapper').querySelector('.ct-status-label');
            fetch('<?= Url::to(['/custom-theme/admin/toggle']) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')
                                    ? document.querySelector('meta[name="csrf-token"]').content
                                    : '<?= Yii::$app->request->csrfToken ?>'
                },
                body: 'key=' + encodeURIComponent(key) + '&active=' + active
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && label) {
                    label.textContent = active ? 'Actif' : 'Inactif';
                    label.className = 'ct-status-label ' + (active ? 'text-success' : 'text-muted');
                }
            });
        });
    });
});
</script>
