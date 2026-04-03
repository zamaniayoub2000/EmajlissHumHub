<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\pdfViewer;

use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\pdfViewer\helpers\PdfHelper;

class Events
{
    public static function onFileHandlerCollection($event)
    {
        /* @var $collection FileHandlerCollection */
        $collection = $event->sender;

        if ($collection->type === FileHandlerCollection::TYPE_VIEW && PdfHelper::isPdfFile($collection->file)) {
            $collection->register(new filehandler\ViewFileHandler());
        }
    }
}
