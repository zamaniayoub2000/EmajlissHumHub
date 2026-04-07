<?php

namespace humhub\modules\customTheme\assets;

use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
    public $sourcePath = '@custom-theme/assets';

    public $css = [
        'css/admin.css',
    ];

    public $js = [
        'js/admin.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
