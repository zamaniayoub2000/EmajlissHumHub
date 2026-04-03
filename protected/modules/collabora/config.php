<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\collabora\Events;

return [
    'id' => 'collabora',
    'class' => 'humhub\modules\collabora\Module',
    'namespace' => 'humhub\modules\collabora',
    'urlManagerRules' => [
        '/collabora-wopi/<fileId:\d+>' => 'collabora/wopi/head',
        'POST /collabora-wopi/<fileId:\d+>/contents' => 'collabora/wopi/post',
        'GET /collabora-wopi/<fileId:\d+>/contents' => 'collabora/wopi/get',
    ],
    'events' => [
        [FileHandlerCollection::class, FileHandlerCollection::EVENT_INIT, [Events::class, 'onFileHandlerCollection']],
    ],
];
