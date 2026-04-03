<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\pdfViewer\assets;

use humhub\components\assets\AssetBundle;

class PdfJsAssets extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@pdf-viewer/vendor/clean-composer-packages/pdf-js';

    /**
     * @inheritdoc
     */
    public $publishOptions = [
        'except' => ['viewer.html'],
    ];
}
