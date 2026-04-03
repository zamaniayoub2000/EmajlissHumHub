<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\pdfViewer\Events;

return [
    'id' => 'pdf-viewer',
    'class' => 'humhub\modules\pdfViewer\Module',
    'namespace' => 'humhub\modules\pdfViewer',
    'events' => [
        [FileHandlerCollection::class, FileHandlerCollection::EVENT_INIT, [Events::class, 'onFileHandlerCollection']],
    ],
];
