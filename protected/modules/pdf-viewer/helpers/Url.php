<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\pdfViewer\helpers;

use humhub\modules\file\models\File;
use yii\helpers\Url as BaseUrl;

class Url extends BaseUrl
{
    public const ROUTE_VIEW = '/pdf-viewer/view';
    public const ROUTE_VIEWER = '/pdf-viewer/view/open';

    public static function toView(File $file): string
    {
        return self::to([self::ROUTE_VIEW, 'guid' => $file->guid]);
    }

    public static function toViewer(File $file): string
    {
        return self::to([self::ROUTE_VIEWER,
            'guid' => $file->guid,
            'file' => $file->getUrl(), // This GET param is required to load a file by JS code of PDF.js viewer
        ]);
    }
}
