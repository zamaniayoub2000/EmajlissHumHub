<?php

/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\homepage\permissions;

use humhub\modules\admin\components\BaseAdminPermission;
use Yii;

class ManageHomepage extends BaseAdminPermission
{
    /**
     * @inheritdoc
     */
    public $defaultAllowedGroups = [];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [];

    protected $id = 'homepage_manage_homepage';

    /**
     * @inheritdoc
     */
    protected $moduleId = 'homepage';

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('HomepageModule.base', 'Can manage homepages');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('HomepageModule.base', 'Allows the users to manage the homepages.');
    }
}
