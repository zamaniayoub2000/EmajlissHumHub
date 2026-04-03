<?php

use humhub\modules\externalHtmlStream\models\MajlissPost;
use humhub\modules\externalHtmlStream\models\ExternalPost;
use humhub\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \humhub\modules\ui\view\components\View $this
 * @var MajlissPost[] $majlissPosts
 * @var ExternalPost[] $externalPosts
 * @var array $stats
 */

$this->title = Yii::t('ExternalHtmlStreamModule.base', 'Majliss Sync — Tableau de bord');
?>

<!-- ═══ Stats rapides ═══ -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-md-3">
        <div class="panel panel-default" style="text-align: center; padding: 15px;">
            <div style="font-size: 28px; font-weight: 700; color: #333;"><?= $stats['total'] ?></div>
            <div class="text-muted">Total synchronisés</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-default" style="text-align: center; padding: 15px;">
            <div style="font-size: 28px; font-weight: 700; color: #28a745;"><?= $stats['success'] ?></div>
            <div class="text-muted">Succès</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-default" style="text-align: center; padding: 15px;">
            <div style="font-size: 28px; font-weight: 700; color: #dc3545;"><?= $stats['errors'] ?></div>
            <div class="text-muted">Erreurs</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-default" style="text-align: center; padding: 15px;">
            <div style="font-size: 28px; font-weight: 700; color: #ffc107;"><?= $stats['pending'] ?></div>
            <div class="text-muted">En attente</div>
        </div>
    </div>
</div>

<!-- ═══ Info mode API ═══ -->
<div class="alert alert-info" style="border-left: 4px solid #667eea;">
    <i class="fa fa-wordpress" style="font-size: 18px; margin-right: 8px;"></i>
    <strong>Mode API REST WordPress</strong> — Les posts sont récupérés via
    <code>GET /wp-json/wp/v2/posts</code> (aucune connexion directe à la base de données).
</div>

<!-- ═══ Section Majliss ═══ -->
<div class="panel panel-default">
    <div class="panel-heading">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h4 class="panel-title" style="margin: 0;">
                <i class="fa fa-wordpress"></i>
                Posts Majliss synchronisés
            </h4>
            <div>
                <?= Button::success(Yii::t('ExternalHtmlStreamModule.base', 'Synchroniser maintenant'))
                    ->link(Url::to(['sync']))
                    ->icon('fa-refresh')
                    ->sm() ?>
                <?php if ($stats['pending'] > 0): ?>
                    <?= Html::a(
                        '<i class="fa fa-check-circle"></i> Publier les ' . $stats['pending'] . ' en attente',
                        ['publish-all-pending'],
                        ['class' => 'btn btn-sm btn-primary', 'data-confirm' => 'Publier tous les posts en attente ?']
                    ) ?>
                    <?= Html::a(
                        '<i class="fa fa-trash"></i> Supprimer en attente',
                        ['delete-all-pending'],
                        ['class' => 'btn btn-sm btn-danger', 'data-confirm' => 'Supprimer tous les posts en attente ?', 'data-method' => 'post']
                    ) ?>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-default" id="btn-test-connection">
                    <i class="fa fa-plug"></i> Tester API
                </button>
                <?= Button::defaultType('Logs')
                    ->link(Url::to(['logs']))
                    ->icon('fa-list-alt')
                    ->sm() ?>
                <?= Button::defaultType('Configuration')
                    ->link(Url::to(['config']))
                    ->icon('fa-cog')
                    ->sm() ?>
            </div>
        </div>
    </div>

    <!-- Résultat test connexion -->
    <div id="connection-test-result" style="display: none; margin: 15px 15px 0;"></div>

    <div class="panel-body">
        <?php if (empty($majlissPosts)): ?>
            <div class="text-center" style="padding: 30px;">
                <i class="fa fa-inbox" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 15px;"></i>
                <p class="text-muted">Aucun post Majliss synchronisé.</p>
                <p class="text-muted">Cliquez sur "Synchroniser maintenant" pour récupérer les posts depuis l'API WordPress.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">WP ID</th>
                            <th>Titre</th>
                            <th style="width: 120px;">Catégorie</th>
                            <th style="width: 100px;">Date WP</th>
                            <th style="width: 70px;">Image</th>
                            <th style="width: 100px;">Statut</th>
                            <th style="width: 130px;">Synchronisé</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($majlissPosts as $post): ?>
                            <tr>
                                <td><code><?= $post->wp_post_id ?></code></td>
                                <td>
                                    <strong><?= Html::encode(mb_substr($post->title, 0, 60)) ?></strong>
                                    <?php if (mb_strlen($post->title) > 60): ?>...<?php endif; ?>
                                    <?php if ($post->sync_error): ?>
                                        <br><small class="text-danger"><i class="fa fa-exclamation-circle"></i> <?= Html::encode(mb_substr($post->sync_error, 0, 80)) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($post->category): ?>
                                        <span class="label label-info"><?= Html::encode($post->category) ?></span>
                                    <?php else: ?>
                                        <small class="text-muted">—</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($post->wp_date): ?>
                                        <small><?= date('d/m/Y', strtotime($post->wp_date)) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($post->image_url)): ?>
                                        <img src="<?= htmlspecialchars($post->image_url) ?>"
                                             style="width: 50px; height: 35px; object-fit: cover; border-radius: 3px;"
                                             loading="lazy">
                                    <?php else: ?>
                                        <small class="text-muted">—</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= $post->getSyncStatusLabel() ?></td>
                                <td>
                                    <?php if ($post->synced_at): ?>
                                        <small class="text-muted">
                                            <?= Yii::$app->formatter->asRelativeTime($post->synced_at) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <?php if ($post->sync_status === MajlissPost::SYNC_PENDING): ?>
                                            <?= Html::a(
                                                '<i class="fa fa-check"></i> Publier',
                                                ['publish-post', 'id' => $post->id],
                                                ['class' => 'btn btn-success', 'title' => 'Publier ce post']
                                            ) ?>
                                        <?php endif; ?>
                                        <?php if ($post->sync_status === MajlissPost::SYNC_ERROR): ?>
                                            <?= Html::a(
                                                '<i class="fa fa-repeat"></i>',
                                                ['retry-sync', 'id' => $post->id],
                                                ['class' => 'btn btn-warning', 'title' => 'Resynchroniser']
                                            ) ?>
                                        <?php endif; ?>
                                        <?= Html::a(
                                            '<i class="fa fa-trash"></i>',
                                            ['delete-majliss', 'id' => $post->id],
                                            [
                                                'class' => 'btn btn-danger',
                                                'title' => 'Supprimer',
                                                'data-confirm' => 'Supprimer ce post synchronisé ?',
                                                'data-method' => 'post',
                                            ]
                                        ) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══ Section Publications HTML externes (API) ═══ -->
<div class="panel panel-default">
    <div class="panel-heading">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h4 class="panel-title" style="margin: 0;">
                <i class="fa fa-globe"></i>
                Publications HTML externes (API)
            </h4>
            <div>
                <?= Button::primary('Nouvelle publication')
                    ->link(Url::to(['create']))
                    ->icon('fa-plus')
                    ->sm() ?>
                <?= Button::defaultType('Rafraîchir tout')
                    ->link(Url::to(['refresh-all']))
                    ->icon('fa-refresh')
                    ->sm() ?>
            </div>
        </div>
    </div>

    <div class="panel-body">
        <?php if (empty($externalPosts)): ?>
            <div class="text-center" style="padding: 20px;">
                <p class="text-muted">Aucune publication HTML externe.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 30px;">ID</th>
                            <th>Titre</th>
                            <th>URL API</th>
                            <th style="width: 90px;">Intervalle</th>
                            <th style="width: 130px;">Dernière MAJ</th>
                            <th style="width: 70px;">Statut</th>
                            <th style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($externalPosts as $post): ?>
                            <tr>
                                <td><?= $post->id ?></td>
                                <td><strong><?= Html::encode($post->title) ?></strong></td>
                                <td>
                                    <code style="font-size: 11px; word-break: break-all;">
                                        <?= Html::encode(mb_substr($post->api_url, 0, 60)) ?>
                                    </code>
                                </td>
                                <td>
                                    <?php
                                    $i = $post->refresh_interval;
                                    echo $i >= 3600 ? round($i / 3600, 1) . 'h' : ($i >= 60 ? round($i / 60) . 'min' : $i . 's');
                                    ?>
                                </td>
                                <td>
                                    <?php if ($post->last_fetched_at): ?>
                                        <small class="text-muted"><?= Yii::$app->formatter->asRelativeTime($post->last_fetched_at) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">—</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($post->cached_html)): ?>
                                        <span class="label label-success"><i class="fa fa-check"></i> OK</span>
                                    <?php else: ?>
                                        <span class="label label-warning"><i class="fa fa-exclamation-triangle"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <?= Html::a('<i class="fa fa-pencil"></i>', ['update', 'id' => $post->id], ['class' => 'btn btn-default', 'title' => 'Modifier']) ?>
                                        <?= Html::a('<i class="fa fa-refresh"></i>', ['refresh', 'id' => $post->id], ['class' => 'btn btn-info', 'title' => 'Rafraîchir']) ?>
                                        <button type="button" class="btn btn-default btn-test-api"
                                                data-url="<?= Url::to(['test-api', 'id' => $post->id]) ?>"
                                                title="Tester l'API">
                                            <i class="fa fa-flask"></i>
                                        </button>
                                        <?= Html::a('<i class="fa fa-trash"></i>', ['delete', 'id' => $post->id], [
                                            'class' => 'btn btn-danger',
                                            'data-confirm' => 'Supprimer cette publication ?',
                                            'data-method' => 'post',
                                        ]) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal test API -->
<div class="modal fade" id="apiTestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-flask"></i> Test API</h4>
            </div>
            <div class="modal-body" id="apiTestResult">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p>Test en cours...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<?php
$testConnectionUrl = Url::to(['test-connection']);
$js = <<<JS
// Test connexion API WordPress
$('#btn-test-connection').on('click', function() {
    var btn = $(this);
    var resultDiv = $('#connection-test-result');
    btn.prop('disabled', true).find('.fa').addClass('fa-spin');
    resultDiv.html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> Appel de l\'API WordPress en cours...</div>').show();

    $.ajax({
        url: '{$testConnectionUrl}',
        type: 'GET',
        dataType: 'json',
        timeout: 30000,
        success: function(data) {
            if (data.success) {
                resultDiv.html(
                    '<div class="alert alert-success">' +
                    '<i class="fa fa-check-circle"></i> ' + data.message +
                    (data.remaining > 0
                        ? ' <span class="label label-warning">' + data.remaining + ' post(s) à synchroniser</span>'
                        : ' <span class="label label-success">Tout est à jour !</span>'
                    ) +
                    '</div>'
                );
            } else {
                resultDiv.html(
                    '<div class="alert alert-danger"><i class="fa fa-times-circle"></i> ' + data.message + '</div>'
                );
            }
        },
        error: function() {
            resultDiv.html('<div class="alert alert-danger"><i class="fa fa-times-circle"></i> Erreur de communication avec le serveur.</div>');
        },
        complete: function() {
            btn.prop('disabled', false).find('.fa').removeClass('fa-spin');
        }
    });
});

// Test API externe (publications HTML)
$(document).on('click', '.btn-test-api', function() {
    var url = $(this).data('url');
    var modal = $('#apiTestModal');
    var resultDiv = $('#apiTestResult');
    resultDiv.html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Test en cours...</p></div>');
    modal.modal('show');

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                resultDiv.html(
                    '<div class="alert alert-success"><i class="fa fa-check"></i> ' + data.message + '</div>' +
                    '<div class="well"><strong>Aperçu :</strong><br>' + data.html_preview + '</div>' +
                    '<small class="text-muted">Récupéré le : ' + data.last_fetched_at + '</small>'
                );
            } else {
                resultDiv.html('<div class="alert alert-danger"><i class="fa fa-times"></i> ' + data.message + '</div>');
            }
        },
        error: function() {
            resultDiv.html('<div class="alert alert-danger"><i class="fa fa-times"></i> Erreur de communication.</div>');
        }
    });
});
JS;
$this->registerJs($js);
?>
