<?php

namespace humhub\modules\externalHtmlStream;

use Yii;
use yii\helpers\Url;
use humhub\modules\content\components\ContentContainerModule;
use humhub\modules\space\models\Space;

/**
 * Module "Majliss Sync" — External HTML Stream
 *
 * Synchronise les posts publiés depuis l'API REST WordPress de Majliss
 * vers le fil d'actualité (stream) d'un espace HumHub.
 *
 * Étend ContentContainerModule pour pouvoir créer du contenu
 * (ContentActiveRecord) dans les espaces HumHub.
 * Le module doit être activé sur chaque espace cible.
 */
class Module extends ContentContainerModule
{
    /** @inheritdoc */
    public $resourcesPath = 'resources';

    // ── Valeurs par défaut (surchargeables via settings) ──

    public $wpApiBaseUrl = 'https://majliscom.csefrs.ma';
    public $wpApiEndpoint = '/wp-json/wp/v2';
    public $wpAuthMethod = 'none';
    public $wpAuthUser = '';
    public $wpAuthPassword = '';
    public $wpJwtToken = '';
    public $batchLimit = 50;
    public $targetSpaceId = 1;
    public $fallbackImage = '';
    public $autoSyncEnabled = true;
    public $apiTimeout = 30;
    public $imageDownloadTimeout = 20;
    public $wpCategoryFilter = '';
    public $imageBaseUrl = '';
    public $categorySpaceMapping = '';
    public $enableCache = true;
    public $allowIframes = false;
    public $whitelistedDomains = '';

    /**
     * @inheritdoc
     * Indique que le module peut être activé sur des Espaces (Space).
     * Cela permet de créer du ContentActiveRecord dans ces espaces.
     */
    public function getContentContainerTypes()
    {
        return [
            Space::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getConfigUrl()
    {
        return Url::to(['/external-html-stream/admin/config']);
    }

    /**
     * @inheritdoc
     * Appelé quand le module est désactivé sur un espace.
     */
    public function disableContentContainer(\humhub\modules\content\components\ContentContainerActiveRecord $container)
    {
        parent::disableContentContainer($container);
    }

    /**
     * @inheritdoc
     * Appelé quand le module est activé sur un espace.
     */
    public function enableContentContainer(\humhub\modules\content\components\ContentContainerActiveRecord $container)
    {
        parent::enableContentContainer($container);
    }

    /**
     * Charge un paramètre depuis les settings du module avec fallback
     * sur la propriété de la classe.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getModuleSetting(string $key, $default = null)
    {
        try {
            $value = $this->settings->get($key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        } catch (\Exception $e) {
            // settings pas encore dispo (ex: pendant l'install)
        }

        // Fallback sur la propriété du module
        if (property_exists($this, $key)) {
            return $this->$key;
        }

        return $default;
    }

    /**
     * Retourne la configuration complète de l'API WordPress.
     *
     * @return array
     */
    public function getWpApiConfig(): array
    {
        return [
            'base_url'    => rtrim($this->getModuleSetting('wpApiBaseUrl', $this->wpApiBaseUrl), '/'),
            'endpoint'    => $this->getModuleSetting('wpApiEndpoint', $this->wpApiEndpoint),
            'auth_method' => $this->getModuleSetting('wpAuthMethod', $this->wpAuthMethod),
            'auth_user'   => $this->getModuleSetting('wpAuthUser', $this->wpAuthUser),
            'auth_pass'   => $this->getModuleSetting('wpAuthPassword', $this->wpAuthPassword),
            'jwt_token'   => $this->getModuleSetting('wpJwtToken', $this->wpJwtToken),
            'timeout'     => (int) $this->getModuleSetting('apiTimeout', $this->apiTimeout),
        ];
    }
}
