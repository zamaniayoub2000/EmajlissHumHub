<?php

use humhub\widgets\TopMenu;
use humhub\modules\eservice\Events;

return [
    'id' => 'eservice',
    'class' => 'humhub\modules\eservice\Module',
    'namespace' => 'humhub\modules\eservice',
    'urlManagerRules' => [
        'eservice' => 'eservice/index/index',
        'eservice/request/<action>' => 'eservice/request/<action>',
        'eservice/admin/<action>' => 'eservice/admin/<action>',
        'eservice/dashboard' => 'eservice/index/dashboard',
    ],
    'modules' => [],
    'events' => [
        [
            'class' => TopMenu::class,
            'event' => TopMenu::EVENT_INIT,
            'callback' => [Events::class, 'onTopMenuInit'],
        ],
    ],
];
