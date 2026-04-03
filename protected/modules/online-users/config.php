<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\dashboard\widgets\Sidebar as DashboardSidebar;
use humhub\modules\onlineUsers\Events;

return [
    'id' => 'online-users',
    'class' => 'humhub\modules\onlineUsers\Module',
    'namespace' => 'humhub\modules\onlineUsers',
    'events' => [
        [DashboardSidebar::class, DashboardSidebar::EVENT_INIT, [Events::class, 'onDashboardSidebarInit']],
    ],
];
