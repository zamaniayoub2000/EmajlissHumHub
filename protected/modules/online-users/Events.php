<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\onlineUsers;

use humhub\modules\onlineUsers\models\Config;
use humhub\modules\onlineUsers\widgets\SidebarWidget;

class Events
{
    public static function onDashboardSidebarInit($event)
    {
        $config = new Config();
        if ($config->isVisibleSidebar()) {
            $event->sender->addWidget(SidebarWidget::class, [], ['sortOrder' => (int) $config->sidebarOrder]);
        }
    }
}
