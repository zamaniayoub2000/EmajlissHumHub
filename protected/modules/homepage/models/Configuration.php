<?php

/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\homepage\models;

use humhub\components\SettingsManager;
use Yii;
use yii\base\Model;

/**
 * @property-read string $defaultContentType
 */
class Configuration extends Model
{
    public SettingsManager $settingsManager;

    public string|bool $groupHomepages = true;

    public string|array $enabledContentTypes = [Homepage::CONTENT_TYPE_DEFAULT];

    public string|array $widgetOrders = [];


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['groupHomepages'], 'boolean'],
            [['widgetOrders', 'enabledContentTypes'], 'safe'],
            [['enabledContentTypes'], 'required', 'message' => Yii::t('HomepageModule.config', 'At least one content type is required')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'groupHomepages' => Yii::t('HomepageModule.config', 'Enable group homepages'),
            'enabledContentTypes' => Yii::t('HomepageModule.config', 'Enabled content types'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'groupHomepages' => Yii::t('HomepageModule.config', 'Allow homepages for specific groups.'),
        ];
    }

    public function loadBySettings(): void
    {
        $this->groupHomepages = (bool)$this->settingsManager->get('groupHomepages', $this->groupHomepages);
        $this->widgetOrders = (array)$this->settingsManager->getSerialized('widgetOrders', $this->widgetOrders);
        $this->enabledContentTypes = (array)$this->settingsManager->getSerialized('enabledContentTypes', $this->enabledContentTypes);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->settingsManager->set('groupHomepages', $this->groupHomepages);
        $this->settingsManager->setSerialized('widgetOrders', $this->widgetOrders);
        $this->settingsManager->setSerialized('enabledContentTypes', $this->enabledContentTypes);

        Homepage::deleteAllCache();

        return true;
    }

    public function getDefaultContentType(): string
    {
        return $this->isContentTypeEnabled(Homepage::CONTENT_TYPE_DEFAULT)
            ? Homepage::CONTENT_TYPE_DEFAULT
            : reset($this->enabledContentTypes);
    }

    public function isContentTypeEnabled(string $contentType): bool
    {
        return in_array($contentType, $this->enabledContentTypes, true);
    }
}
