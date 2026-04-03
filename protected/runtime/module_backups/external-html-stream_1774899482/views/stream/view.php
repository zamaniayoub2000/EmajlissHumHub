<?php

use humhub\modules\externalHtmlStream\widgets\ExternalHtmlWidget;
use humhub\modules\externalHtmlStream\models\MajlissPost;
use yii\helpers\Html;

/**
 * @var \humhub\modules\ui\view\components\View $this
 * @var \humhub\modules\externalHtmlStream\models\ExternalPost|\humhub\modules\externalHtmlStream\models\MajlissPost $model
 */

$this->title = $model->title;
?>

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <?php if ($model instanceof MajlissPost): ?>
                <!-- Affichage MajlissPost -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4><?= Html::encode($model->title) ?></h4>
                        <?php if ($model->category): ?>
                            <span class="label label-primary"><?= Html::encode($model->category) ?></span>
                        <?php endif; ?>
                        <?php if ($model->wp_date): ?>
                            <small class="text-muted">
                                <i class="fa fa-calendar"></i> <?= date('d/m/Y', strtotime($model->wp_date)) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                    <div class="panel-body">
                        <?= $model->getFormattedContent() ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Affichage ExternalPost -->
                <?= ExternalHtmlWidget::widget([
                    'model' => $model,
                    'showTitle' => true,
                    'showRefreshButton' => true,
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
