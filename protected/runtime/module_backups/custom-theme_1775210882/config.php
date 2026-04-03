<?php

use Yii;
use humhub\modules\customTheme\Events;
use humhub\modules\admin\widgets\AdminMenu;

// Enregistrement de l'alias pour le module
Yii::setAlias('@custom-theme', __DIR__);

return [
    'id' => 'custom-theme',
    'class' => 'humhub\modules\customTheme\Module',
    'namespace' => 'humhub\modules\customTheme',
    'events' => [
        // Injection du footer et header dans toutes les pages
        [
            'class' => \humhub\components\View::class,
            'event' => \humhub\components\View::EVENT_END_BODY,
            'callback' => [Events::class, 'onEndBody'],
        ],
        [
            'class' => \humhub\components\View::class,
            'event' => \humhub\components\View::EVENT_BEGIN_BODY,
            'callback' => [Events::class, 'onBeginBody'],
        ],
        // Injection CSS/JS custom
        [
            'class' => \humhub\components\View::class,
            'event' => \humhub\components\View::EVENT_BEFORE_RENDER,
            'callback' => [Events::class, 'onBeforeRender'],
        ],
        // Menu admin
        [
            'class' => AdminMenu::class,
            'event' => AdminMenu::EVENT_INIT,
            'callback' => [Events::class, 'onAdminMenuInit'],
        ],
    ],
];
