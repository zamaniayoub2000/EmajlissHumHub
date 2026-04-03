<?php

/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\homepage;

use humhub\modules\homepage\models\Configuration;
use humhub\modules\homepage\models\Homepage;
use humhub\modules\homepage\permissions\ManageHomepage;
use Yii;
use yii\db\StaleObjectException;
use yii\helpers\Url;

/**
 *
 * @property-read string $configUrl
 * @property-read Configuration $configuration
 */
class Module extends \humhub\components\Module
{
    /**
     * @var string defines the icon
     */
    public $icon = 'home';

    private ?Configuration $_configuration = null;

    public function getConfiguration(): Configuration
    {
        if ($this->_configuration === null) {
            $this->_configuration = new Configuration(['settingsManager' => $this->settings]);
            $this->_configuration->loadBySettings();
        }
        return $this->_configuration;
    }

    /**
     * @inheridoc
     */
    public function getName(): string
    {
        return Yii::t('HomepageModule.base', 'Homepage');
    }

    /**
     * @inheridoc
     */
    public function getDescription(): string
    {
        return Yii::t('HomepageModule.base', 'Create custom homepages for registered users, guests, and for members of specific groups');
    }

    /**
     * @inerhitdoc
     */
    public function disable()
    {
        if (Yii::$app->db->schema->getTableSchema(Homepage::tableName()) !== null) {
            /** @var Homepage $homepage */
            foreach (Homepage::find()->each() as $homepage) {
                try {
                    $homepage->delete();
                } catch (StaleObjectException|\Throwable $e) {
                }
            }
        }

        parent::disable();
    }

    /**
     * @inheritdoc
     */
    public function getConfigUrl(): string
    {
        return Url::to(['/homepage/config/index']);
    }

    /**
     * @inheritdoc
     * Replace with `getContainerPermissions` if this model extends `ContentContainerModule` and you want permissions only for containers this module is enabled on.
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer === null) {
            return [new ManageHomepage()];
        }

        return [];
    }
}
