<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\onlineUsers\widgets;

use humhub\components\Widget;
use humhub\helpers\Html;
use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\onlineUsers\models\Config;
use humhub\modules\onlineUsers\Module;
use humhub\modules\onlineUsers\services\UserService;
use humhub\widgets\bootstrap\Link;
use Yii;

/**
 * SidebarWidget shows online users inside the dashboard sidebar.
 */
class SidebarWidget extends Widget
{
    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        return parent::beforeRun() && Config::instance()->isVisibleSidebar();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $userService = new UserService();
        if ($userService->getCount() === 0) {
            return '';
        }

        return $this->render('sidebar', [
            'total' => $userService->getCount(),
            'users' => $userService->getUsers(Config::instance()->limit),
            'extraMenus' => $this->getExtraMenus(),
        ]);
    }

    private function getExtraMenus(): string
    {
        if (!Yii::$app->user->can(ManageModules::class)) {
            return '';
        }

        return Html::tag(
            'li',
            Link::to(Yii::t('OnlineUsersModule.base', 'Settings'))
                ->link(Module::getInstance()->getConfigUrl())
                ->icon('cog')
                ->cssClass(['btn', 'dropdown-item']),
        );
    }
}
