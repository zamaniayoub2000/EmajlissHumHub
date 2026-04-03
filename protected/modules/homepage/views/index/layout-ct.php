<?php
/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\modules\dashboard\Module as DashboardModule;
use humhub\modules\homepage\models\Homepage;
use humhub\modules\homepage\widgets\HomepageContent;
use humhub\components\View;
use humhub\modules\user\Module as UserModule;

/**
 * @var $this View
 * @var $homepage Homepage
 */

/** @var DashboardModule $dashboardModule */
$dashboardModule = Yii::$app->getModule('dashboard');
/** @var UserModule $userModule */
$userModule = Yii::$app->getModule('user');

$sidebarWidgets = $homepage->getSidebarWidgets();
?>

<div id="homepage-layout-ct">
    <?= HomepageContent::widget(['homepage' => $homepage]) ?>
</div>
