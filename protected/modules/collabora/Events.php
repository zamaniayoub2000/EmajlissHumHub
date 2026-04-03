<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\collabora;

use humhub\modules\collabora\services\FileAclService;
use humhub\modules\file\handler\FileHandlerCollection;
use Yii;

class Events
{
    public static function onFileHandlerCollection($event)
    {
        /* @var $collection FileHandlerCollection */
        $collection = $event->sender;

        /* @var $module Module */
        $module = Yii::$app->getModule('collabora');

        if ($collection->type === FileHandlerCollection::TYPE_CREATE) {
            $collection->register(new filehandler\CreateFileHandler());
        }

        if (!$module->isSupportedType($collection->file)) {
            return;
        }

        if ($collection->type == FileHandlerCollection::TYPE_EDIT && $collection->file->canDelete()) {
            $collection->register(new filehandler\EditFileHandler());
        }

        if ($collection->type == FileHandlerCollection::TYPE_VIEW && $collection->file->canView()) {
            // Don't show the view button for users who can edit the file (to avoid confusion)
            // If we want to have a View mode for users with write permissions, we need to add a view only flag
            // to the Access Token and handle this in the WopiController::actionHead() - canWrite.
            if (!$collection->file->canDelete()) {
                $collection->register(new filehandler\ViewFileHandler());
            }
        }
    }

}
