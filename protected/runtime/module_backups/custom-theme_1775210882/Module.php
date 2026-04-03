<?php

namespace humhub\modules\customTheme;

use Yii;
use yii\helpers\Url;

/**
 * Module Custom Theme - Personnalisation complète du thème HumHub
 *
 * Permet de surcharger le header, footer, CSS et JS du thème Clean
 * avec une interface d'administration complète.
 */
class Module extends \humhub\components\Module
{
    /**
     * @inheritdoc
     */
    public $resourcesPath = 'resources';

    /**
     * @inheritdoc
     */
    public function getConfigUrl()
    {
        return Url::to(['/custom-theme/admin/index']);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return Yii::t('CustomThemeModule.base', 'Custom Theme Override');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('CustomThemeModule.base', 'Personnalisation complète du thème : header, footer, CSS et JS avec interface d\'administration.');
    }

    /**
     * @inheritdoc
     */
    public function disable()
    {
        parent::disable();
    }

    /**
     * @inheritdoc
     */
    public function enable()
    {
        if (parent::enable()) {
            return true;
        }
        return false;
    }
}
