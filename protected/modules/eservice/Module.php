<?php

namespace humhub\modules\eservice;

use Yii;

class Module extends \humhub\components\Module
{
    /**
     * @var int Maximum upload file size in bytes (default 10MB)
     */
    public $uploadMaxSize = 10485760;

    /**
     * @var string Path to module resources
     */
    public $resourcesPath = 'resources';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Register the @eservice alias so asset bundles can locate resources
        Yii::setAlias('@eservice', __DIR__);
    }

    /**
     * @inheritdoc
     */
    public function getContentContainerTypes()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function disable()
    {
        parent::disable();
    }
}
