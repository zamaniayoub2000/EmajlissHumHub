<?php

use humhub\modules\externalHtmlStream\Module;
use humhub\modules\externalHtmlStream\Events;

return [
    'id' => 'external-html-stream',
    'class' => Module::class,
    'namespace' => 'humhub\modules\externalHtmlStream',
    'events' => [
        // Synchronisation automatique via le cron horaire HumHub
        [
            'class' => \humhub\commands\CronController::class,
            'event' => \humhub\commands\CronController::EVENT_ON_HOURLY_RUN,
            'callback' => [Events::class, 'onCronRun'],
        ],
    ],
];
