<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\collabora\models;

use humhub\components\SettingsManager;
use humhub\libs\SafeBaseUrl;
use humhub\modules\collabora\services\CollaboraService;
use Yii;
use yii\base\Model;

/**
 * Form for Text Editor Module Settings
 */
class Configuration extends Model
{
    public string $wopiHostBaseUrl = '';
    public string $wopiClientBaseUrl = '';

    public ?SettingsManager $settingsManager = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['wopiClientBaseUrl'],
                'url',
                'pattern' => '/^{schemes}:\/\/(([A-Z0-9][A-Z0-9_-]*)((\.)?[A-Z0-9][A-Z0-9_-]*)+)(?::\d{1,5})?(?:$|[?\/#])/i',
            ],
            [
                'wopiClientBaseUrl',
                function ($attribute, $params): void {
                    $collaboraService = new CollaboraService($this);
                    try {
                        if (empty($collaboraService->getWopiBaseUrl())) {
                            $this->addError($attribute, 'Empty response from Collabora Online server.');
                            return;
                        }
                    } catch (\Exception $exception) {
                        $this->addError(
                            $attribute,
                            'Could not connect to Collabora Online server. (' . $exception->getMessage() . ')',
                        );
                        return;
                    }
                },
            ],
            [['wopiHostBaseUrl'], 'url'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'wopiClientBaseUrl' => Yii::t('CollaboraModule.base', 'Base URL of Collabora Online (WOPI Client Server)'),
            'wopiHostBaseUrl' => Yii::t('CollaboraModule.base', 'Base URL of HumHub (WOPI Host)'),
        ];
    }

    public function attributeHints()
    {
        return [
            'wopiClientBaseUrl' => Yii::t(
                'CollaboraModule.base',
                'The URL of the Collabora Online server that provides the editing functionality. Example: https://collabora.example.com',
            ),
            'wopiHostBaseUrl' => Yii::t(
                'CollaboraModule.base',
                'Optional. URL at which the HumHub installation for Collabora can be accessed. By default, the configured base URL is used. Default: ' . SafeBaseUrl::base(
                    true,
                ),
            ),
        ];
    }

    public function isConfigured()
    {
        if (!Yii::$app->urlManager->enablePrettyUrl) {
            return false;
        }

        return !empty($this->wopiClientBaseUrl);
    }


    public function loadBySettings(): void
    {
        $this->wopiClientBaseUrl = (string)$this->settingsManager->get('wopiClientBaseUrl');
        $this->wopiHostBaseUrl = (string)$this->settingsManager->get('wopiHostBaseUrl');
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->settingsManager->set('wopiClientBaseUrl', $this->wopiClientBaseUrl);
        $this->settingsManager->set('wopiHostBaseUrl', $this->wopiHostBaseUrl);

        return true;
    }
}
