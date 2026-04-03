<?php
/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

/**
 * @var $this View
 * @var $searchModel GroupSearch
 * @var $dataProvider ActiveDataProvider
 */

use humhub\components\View;
use humhub\modules\admin\models\GroupSearch;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\homepage\models\forms\HomepageForm;
use humhub\modules\homepage\models\Homepage;
use humhub\modules\homepage\Module;
use humhub\modules\user\models\Group;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\GridView;
use humhub\widgets\modal\ModalButton;
use yii\data\ActiveDataProvider;

/** @var Module $module */
$module = Yii::$app->getModule('homepage');
$homepageUrl = Homepage::getUrlForUser()
?>

<div class="panel panel-default">
    <div class="panel-heading">

        <?= Yii::$app->user->can(ManageSettings::class) ? Button::light(Yii::t('HomepageModule.admin', 'Settings'))
            ->link($module->getConfigUrl())
            ->icon('gears')
            ->style('margin-left: 6px;')
            ->right()
            ->sm() : '' ?>

        <?= $homepageUrl ? Button::info(Yii::t('HomepageModule.admin', 'View homepage'))
            ->link($homepageUrl)
            ->icon($module->icon)
            ->style('margin-left: 6px;')
            ->right()
            ->sm() : '' ?>

        <strong><?= $module->getName() ?></strong>

        <div class="text-body-secondary">
            <?= $module->getDescription() ?>
        </div>
    </div>
    <div class="panel-body">
        <h4>
            <?= Yii::t('HomepageModule.admin', 'Homepage for guests') ?>
            <?= Homepage::enabledLabel(Homepage::TARGET_GUEST) ?>
        </h4>

        <?= ModalButton::primary(Yii::t('HomepageModule.admin', 'Edit'))
            ->icon('gear')
            ->load(['/homepage/admin/edit', 'target' => Homepage::TARGET_GUEST]) ?>

        <?= HomepageForm::getPreviewBtn(Homepage::TARGET_GUEST) ?>

        <br><br>

        <h4>
            <?= Yii::t('HomepageModule.admin', 'Homepage for registered users') ?>
            <?= Homepage::enabledLabel(Homepage::TARGET_REGISTERED) ?>
        </h4>

        <?= ModalButton::primary(Yii::t('HomepageModule.admin', 'Edit'))
            ->icon('gear')
            ->load(['/homepage/admin/edit', 'target' => Homepage::TARGET_REGISTERED]) ?>

        <?= HomepageForm::getPreviewBtn(Homepage::TARGET_REGISTERED) ?>

        <br><br>

        <?php if ($module->configuration->groupHomepages) : ?>
            <h4><?= Yii::t('HomepageModule.admin', 'Homepages for groups') ?></h4>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'tableOptions' => ['class' => 'table table-hover'],
                'columns' => [
                    ['attribute' => 'name'],
                    [
                        'header' => Yii::t('HomepageModule.admin', 'Members'),
                        'format' => 'raw',
                        'options' => ['style' => 'text-align:center;'],
                        'value' => static function (Group $data) {
                            return $data->getGroupUsers()->count();
                        }
                    ],
                    [
                        'format' => 'raw',
                        'options' => ['style' => 'text-align:center;'],
                        'value' => static function (Group $data) {
                            return Homepage::enabledLabel(Homepage::TARGET_GROUP, $data->id);
                        }
                    ],
                    [
                        'header' => Yii::t('HomepageModule.admin', 'Priority order'),
                        'options' => ['style' => 'text-align:center;'],
                        'value' => static function (Group $data) {
                            $homepage = Homepage::getQuery(Homepage::TARGET_GROUP, $data->id)->one();
                            return $homepage->group_priority_order ?? '';
                        }
                    ],
                    [
                        'format' => 'raw',
                        'options' => ['style' => 'text-align:center;'],
                        'value' => static function (Group $data) {
                            return ModalButton::primary(Yii::t('HomepageModule.admin', 'Edit'))
                                    ->icon('gear')
                                    ->load(['/homepage/admin/edit', 'target' => Homepage::TARGET_GROUP, 'groupId' => $data->id]) .
                                ' ' .
                                HomepageForm::getPreviewBtn(Homepage::TARGET_GROUP, $data->id);
                        }
                    ],
                ],
            ]) ?>
        <?php endif ?>
    </div>
</div>
