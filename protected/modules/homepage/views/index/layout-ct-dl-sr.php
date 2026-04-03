<?php
/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\modules\dashboard\Module;
use humhub\modules\dashboard\widgets\DashboardContent;
use humhub\modules\homepage\models\Homepage;
use humhub\modules\homepage\widgets\HomepageContent;
use humhub\components\View;
use humhub\widgets\BaseSidebar;
use humhub\widgets\FooterMenu;

/**
 * @var $this View
 * @var $homepage Homepage
 */

/** @var Module $dashboardModule */
$dashboardModule = Yii::$app->getModule('dashboard');
$sidebarWidgets = $homepage->getSidebarWidgets();
?>

<div id="homepage-layout-ct-dl-sr">

    <?= HomepageContent::widget(['homepage' => $homepage]) ?>

    <div class="row">
        <div class="col-lg-<?= $sidebarWidgets ? '8' : '12' ?> layout-content-container">
            <?php if ($homepage->hasWidget(Homepage::WIDGET_DASHBOARD_STREAM)) : ?>
                <?= DashboardContent::widget([
                    'contentContainer' => Yii::$app->user->identity,
                    'showProfilePostForm' => !Yii::$app->user->isGuest && $dashboardModule->settings->get('showProfilePostForm')
                ]) ?>
            <?php endif; ?>
        </div>

        <?php if ($sidebarWidgets) : ?>
            <div class="col-lg-4 layout-sidebar-container">
                <?= BaseSidebar::widget(['widgets' => $sidebarWidgets]) ?>

                <?php if ($homepage->hasWidget(Homepage::WIDGET_FOOTER)) : ?>
                    <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_SIDEBAR]) ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$sidebarWidgets) : ?>
        <?= FooterMenu::widget(['location' => FooterMenu::LOCATION_FULL_PAGE]) ?>
    <?php endif; ?>
</div>
