<?php

use humhub\modules\customTheme\Module;
use humhub\modules\customTheme\Events;
use humhub\modules\admin\widgets\AdminMenu;

return [
    'id' => 'custom-theme',
    'class' => Module::class,
    'namespace' => 'humhub\modules\customTheme',
    'events' => [
        [
            'class' => \humhub\components\View::class,
            'event' => \humhub\components\View::EVENT_END_BODY,
            'callback' => [Events::class, 'onEndBody'],
        ],
        [
            'class' => \humhub\components\View::class,
            'event' => \humhub\components\View::EVENT_BEFORE_RENDER,
            'callback' => [Events::class, 'onBeforeRender'],
        ],
        [
            'class' => AdminMenu::class,
            'event' => AdminMenu::EVENT_INIT,
            'callback' => [Events::class, 'onAdminMenuInit'],
        ],
    ],
];
