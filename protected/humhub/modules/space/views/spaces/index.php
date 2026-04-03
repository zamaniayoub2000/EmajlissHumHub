<?php
/**
 * @link https://www.humhub.org/
 */

use humhub\assets\CardsAsset;
use humhub\helpers\Html;
use humhub\helpers\ThemeHelper;
use humhub\modules\space\components\SpaceDirectoryQuery;
use humhub\modules\space\widgets\SpaceDirectoryCard;
use yii\web\View;

/* @var $this View */
/* @var $spaces SpaceDirectoryQuery */

CardsAsset::register($this);
?>

<style>
/* ── Add space between header and cards ───────────────── */
.panel.panel-default {
    margin-bottom: 40px; /* adjust: 30px / 50px / 60px */
}

</style>

<div class="panel panel-default">

    <div class="panel-heading">
        <?= Yii::t('SpaceModule.base', '<strong>Espaces de travail et de collaboration</strong>'); ?>
    </div>

    <!-- ✅ Search removed -->

</div>

<div class="container<?= ThemeHelper::isFluid() ? '-fluid' : '' ?> gx-0 overflow-x-hidden">
    <div class="row cards">
        <?php if (!$spaces->exists()): ?>
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <strong><?= Yii::t('SpaceModule.base', 'No results found!'); ?></strong><br/>
                        <?= Yii::t('SpaceModule.base', 'Try other keywords or remove filters.'); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php foreach ($spaces->with('contentContainerRecord')->all() as $space) : ?>
            <?= SpaceDirectoryCard::widget(['space' => $space]); ?>
        <?php endforeach; ?>
    </div>
</div>

<?php if (!$spaces->isLastPage()) : ?>
    <?= Html::tag('div', '', [
        'class' => 'cards-end',
        'data-current-page' => $spaces->pagination->getPage() + 1,
        'data-total-pages' => $spaces->pagination->getPageCount(),
    ]) ?>
<?php endif; ?>