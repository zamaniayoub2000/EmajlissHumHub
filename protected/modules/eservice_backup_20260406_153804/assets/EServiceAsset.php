<?php

namespace humhub\modules\eservice\assets;

use yii\web\AssetBundle;

/**
 * EServiceAsset provides the CSS and JavaScript resources for the E-Service module.
 *
 * @package humhub\modules\eservice\assets
 */
class EServiceAsset extends AssetBundle
{
    /**
     * @var string the directory that contains the source asset files for this asset bundle.
     */
    public $sourcePath = '@eservice/resources';

    /**
     * @var array list of CSS files that this bundle contains.
     */
    public $css = [
        'css/eservice.css',
    ];

    /**
     * @var array list of JavaScript files that this bundle contains.
     */
    public $js = [
        'js/eservice.js',
    ];

    /**
     * @var array list of bundle class names that this bundle depends on.
     */
    public $depends = [
        'humhub\assets\AppAsset',
    ];
}
