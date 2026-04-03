<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\onlineUsers\Module;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image;
use humhub\widgets\PanelMenu;

/* @var User[] $users */
/* @var int $total */
/* @var string $extraMenus */
?>
<div class="panel panel-default">
    <?= PanelMenu::widget(['id' => 'panel-online-users', 'extraMenus' => $extraMenus]) ?>
    <div class="panel-heading">
        <?= Module::getInstance()->getIcon() ?>
        <?= Yii::t('OnlineUsersModule.base', '<strong>Online</strong> users') ?>
        (<?= $total ?>)
    </div>
    <div class="panel-body">
        <?php foreach ($users as $user) : ?>
            <?= Image::widget([
                'user' => $user,
                'width' => 32,
                'showTooltip' => true,
                'showSelfOnlineStatus' => true,
            ]) ?>
        <?php endforeach; ?>
    </div>
</div>
