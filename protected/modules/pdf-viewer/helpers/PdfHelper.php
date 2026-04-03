<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\pdfViewer\helpers;

use humhub\modules\file\models\File;

class PdfHelper
{
    public static function isPdfFile(?File $file): bool
    {
        return $file !== null && $file->mime_type === 'application/pdf';
    }

    public static function canView(File $file): bool
    {
        return self::isPdfFile($file)
            && $file->canView()
            && is_readable($file->getStore()->get());
    }
}
