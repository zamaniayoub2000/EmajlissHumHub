<?php

namespace humhub\modules\externalHtmlStream\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle pour le module External HTML Stream + Majliss Sync.
 */
class ExternalHtmlStreamAsset extends AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@external-html-stream/resources';

    /** @inheritdoc */
    public $css = [
        'css/external-html-stream.css',
    ];

    /** @inheritdoc */
    public $js = [
        'js/external-html-stream.js',
    ];

    /** @inheritdoc */
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
