<?php

namespace humhub\modules\customTheme;

use Yii;
use yii\helpers\Url;

class Module extends \humhub\components\Module
{
    /** @inheritdoc */
    public $resourcesPath = 'resources';

    /** @inheritdoc */
    public function init()
    {
        parent::init();
        Yii::setAlias('@custom-theme', __DIR__);
    }

    /** @inheritdoc */
    public function getConfigUrl()
    {
        return Url::to(['/custom-theme/admin/index']);
    }

    /** @inheritdoc */
    public function getName()
    {
        return Yii::t('CustomThemeModule.base', 'Custom Theme Override');
    }

    /** @inheritdoc */
    public function getDescription()
    {
        return Yii::t('CustomThemeModule.base', 'Personnalisation du thème : footer, header, CSS et JS.');
    }

    /** @inheritdoc */
    public function disable()
    {
        parent::disable();
    }

    /** @inheritdoc */
    public function enable()
    {
        if (parent::enable()) {
            return true;
        }
        return false;
    }
}
