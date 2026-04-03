<?php

namespace humhub\modules\collabora\services;

use humhub\libs\SafeBaseUrl;
use humhub\modules\collabora\models\Configuration;
use humhub\modules\collabora\Module;
use humhub\modules\file\models\File;
use humhub\modules\user\models\User;
use Yii;

final class CollaboraService
{
    private Configuration $config;

    public function __construct(?Configuration $config = null)
    {
        if ($config === null) {
            /** @var Module $module */
            $module = Yii::$app->getModule('collabora');
            $this->config = $module->getConfiguration();
        } else {
            $this->config = $config;
        }
    }

    public function buildUrl(File $file, User $user): string
    {
        return $this->getWopiBaseUrl()
            . 'WOPISrc=' . urlencode($this->getWopiSrcBaseUrl() . '/collabora-wopi/' . $file->id)
            . '&access_token=' . urlencode((string) (new TokenService($file))->getAccessToken($user))
            . '&lang=' . urlencode(Yii::$app->language);
    }

    public function getWopiSrcBaseUrl(): string
    {
        if (!empty($this->config->wopiHostBaseUrl)) {
            return rtrim($this->config->wopiHostBaseUrl, '/');
        }

        return SafeBaseUrl::base(true);
    }

    public function getWopiBaseUrl(): string
    {
        $discoveryUrl = $this->config->wopiClientBaseUrl . '/hosting/discovery';
        $discovery = file_get_contents($discoveryUrl);
        $discovery_parsed = simplexml_load_string($discovery);
        $result = $discovery_parsed->xpath(sprintf('/wopi-discovery/net-zone/app[@name=\'%s\']/action', 'text/plain'));
        if ($result && count($result) > 0) {
            return $result[0]['urlsrc'];
        }

        return '';
    }
}
