<?php
/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\components\View;
use humhub\modules\homepage\models\Configuration;
use humhub\modules\homepage\models\forms\HomepageForm;
use humhub\modules\homepage\models\Homepage;
use humhub\modules\homepage\Module;
use humhub\widgets\bootstrap\Alert;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;

/**
 * @var $this View
 * @var $model Configuration
 */

/** @var Module $module */
$module = Yii::$app->getModule('homepage');

/** @var \humhub\modules\menuManager\Module $menuManagerModule */
$menuManagerModule = Yii::$app->getModule('menu-manager');

/** @var \humhub\modules\custom_pages\Module $customPagesModule */
$customPagesModule = Yii::$app->getModule('custom_pages');
?>


<div class="panel panel-default">
    <div class="panel-heading">

        <?= Button::light(Yii::t('HomepageModule.config', 'Manage homepages'))
            ->link(['/homepage/admin/index'])
            ->icon($module->icon)
            ->style('margin-left: 6px;')
            ->right()
            ->sm() ?>

        <?php if ($menuManagerModule?->isEnabled) : ?>
            <?= Button::light(Yii::t('HomepageModule.config', 'Manage menu'))
                ->link($menuManagerModule->getConfigUrl())
                ->icon($menuManagerModule->icon)
                ->style('margin-left: 6px;')
                ->right()
                ->sm() ?>
        <?php endif; ?>

        <strong><?= $module->getName() ?></strong>

        <div class="text-body-secondary">
            <?= $module->getDescription() ?>
        </div>
    </div>

    <div class="panel-body">
        <?= Alert::info(Yii::t('HomepageModule.config', 'You can allow users to manage homepages in the group permissions.'))->icon('info-circle') ?>

        <?php if ($menuManagerModule?->isEnabled) : ?>
            <?= Alert::info(
                Yii::t('HomepageModule.config', 'To add a "Home" item to the top menu, you can use the {MenuManagerModule} module or the {CustomPagesModule} module.', [
                    'MenuManagerModule' => Button::asLink($menuManagerModule->getName())->link('https://marketplace.humhub.com/module/menu-manager')->options(['target' => '_blank']),
                    'CustomPagesModule' => Button::asLink($customPagesModule ? $customPagesModule->getName() : 'Custom Pages')->link('https://marketplace.humhub.com/module/custom_pages')->options(['target' => '_blank']),
                ]),
            )->icon('wrench') ?>
        <?php endif; ?>

        <?php if (!$customPagesModule?->isEnabled) : ?>
            <?= Alert::warning(
                Yii::t('HomepageModule.config', 'HTML content type is not available. Please install and enable the {CustomPagesModule} module if you want to use it.', [
                    'CustomPagesModule' => Button::asLink('Custom Pages')->link('https://marketplace.humhub.com/module/custom_pages')->options(['target' => '_blank']),
                ]),
            )->icon('exclamation-triangle') ?>
        <?php endif; ?>

        <?php $form = ActiveForm::begin(['acknowledge' => true]); ?>

        <?= $form->field($model, 'groupHomepages')->checkbox() ?>

        <?= $form->field($model, 'enabledContentTypes')->checkboxList(HomepageForm::getContentTypeOptions(false)) ?>

        <label class="form-label" for="configuration-widgetorders-widget-orders">
            <?= Yii::t('HomepageModule.config', 'Widget orders in the homepage sidebar (between 0 and 10000):') ?>
        </label>
        <ul id="configuration-widgetorders-widget-orders" style="list-style-type: none;">
            <?php foreach ((new HomepageForm())->getWidgetOptions() as $widget => $label) : ?>
                <?php if ($widget === Homepage::WIDGET_CUSTOM_PAGES): ?>
                    <li>
                        <label class="form-label" for="configuration-widgetorders-custom_pages">
                            <?= $label ?>
                        </label>
                        <div id="configuration-widgetorders-custom_pages"><?= Yii::t('HomepageModule.config', 'The order is defined in the Custom Pages administration.') ?></div>
                        <br>
                    </li>
                <?php else: ?>
                    <li><?= $form->field($model, 'widgetOrders[' . $widget . ']')->textInput(['type' => 'number'])->label($label) ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
        <?= Button::save()->submit() ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
