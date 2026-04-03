<?php

namespace humhub\modules\customTheme\assets;

use yii\web\AssetBundle;

/**
 * Asset Bundle pour l'interface d'administration du module Custom Theme
 * Enregistre les fichiers CSS et JS nécessaires dans le backoffice
 */
class AdminAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@custom-theme/assets';

    /**
     * @inheritdoc
     */
    public $css = [
        'css/admin.css',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/admin.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
