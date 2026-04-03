<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\pdfViewer\assets;

use humhub\components\assets\AssetBundle;

class PdfViewerAssets extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@pdf-viewer/resources';

    /**
     * @inheritdoc
     */
    public $css = ['css/pdf-viewer.css'];
}
