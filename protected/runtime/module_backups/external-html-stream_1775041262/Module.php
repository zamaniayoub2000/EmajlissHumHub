<?php

namespace humhub\modules\externalHtmlStream;

use Yii;
use yii\helpers\Url;
use humhub\modules\content\components\ContentContainerModule;

/**
 * Module "Majliss Sync" — External HTML Stream
 *
 * Synchronise les posts publiés depuis l'API REST WordPress de Majliss
 * vers le fil d'actualité (stream) d'un espace HumHub.
 *
 * Architecture : consomme l'API WP REST (wp-json/wp/v2/)
 * au lieu d'une connexion directe à la base de données.
 *
 * Fonctionnalités :
 *  - Appel API REST WordPress (GET /wp-json/wp/v2/posts)
 *  - Récupération des posts publiés avec images via l'API médias
 *  - Création automatique dans le stream HumHub (ContentActiveRecord)
 *  - Suivi des posts déjà synchronisés (anti-doublon)
 *  - Cron automatique via Events HumHub
 *  - Backoffice complet (CRUD + config + logs)
 */
class Module extends ContentContainerModule
{
    /** @inheritdoc */
    public $resourcesPath = 'resources';

    // ── Paramètres API REST WordPress Majliss ──

    /** @var string URL de base de l'API WordPress REST (sans /wp-json) */
    public $wpApiBaseUrl = 'https://majliscom.csefrs.ma';

    /** @var string Endpoint personnalisé (vide = /wp-json/wp/v2/) */
    public $wpApiEndpoint = '/wp-json/wp/v2';

    /** @var string Méthode d'authentification : 'none', 'basic', 'jwt', 'application_password' */
    public $wpAuthMethod = 'none';

    /** @var string Utilisateur WP pour l'authentification Basic ou Application Password */
    public $wpAuthUser = '';

    /** @var string Mot de passe / Application Password / JWT Token */
    public $wpAuthPassword = '';

    /** @var string JWT Token si méthode JWT */
    public $wpJwtToken = '';

    // ── Paramètres synchronisation ──

    /** @var int Nombre max de posts par sync (per_page dans l'API) */
    public $batchLimit = 10;

    /** @var int ID de l'espace HumHub cible */
    public $targetSpaceId = 1;

    /** @var string Image de remplacement si pas de miniature */
    public $fallbackImage = '';

    /** @var bool Activer la synchronisation automatique (cron) */
    public $autoSyncEnabled = true;

    /** @var int Timeout pour les appels API (secondes) */
    public $apiTimeout = 30;

    /** @var int Timeout pour le téléchargement des images (secondes) */
    public $imageDownloadTimeout = 20;

    /** @var string Catégories WP à synchroniser (IDs séparés par virgule, vide = toutes) */
    public $wpCategoryFilter = '';

    /** @var string URL de base pour normaliser les images dans le contenu */
    public $imageBaseUrl = 'https://intranet.csefrs.ma';

    // ── Paramètres sécurité HTML ──

    /** @var bool Activer le cache du contenu */
    public $enableCache = true;

    /** @var bool Autoriser les iframes dans le contenu */
    public $allowIframes = false;

    /** @var string Domaines autorisés (séparés par virgule) */
    public $whitelistedDomains = '';

    /** @var array Balises HTML autorisées dans le contenu externe */
    public $allowedHtmlTags = [
        'div', 'span', 'p', 'br', 'hr',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'a', 'img', 'strong', 'em', 'b', 'i', 'u',
        'blockquote', 'pre', 'code',
        'style', 'figure', 'figcaption',
    ];

    /**
     * @inheritdoc
     */
    public function getContentContainerTypes()
    {
        return [
            \humhub\modules\space\models\Space::class,
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
     * Charge un paramètre depuis les settings du module avec fallback.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        $value = $this->settings->get($key);
        if ($value === null || $value === '') {
            return $default ?? $this->$key ?? null;
        }
        return $value;
    }

    /**
     * Retourne la configuration complète de l'API WordPress.
     *
     * @return array
     */
    public function getWpApiConfig(): array
    {
        return [
            'base_url'    => rtrim($this->getSetting('wpApiBaseUrl', $this->wpApiBaseUrl), '/'),
            'endpoint'    => $this->getSetting('wpApiEndpoint', $this->wpApiEndpoint),
            'auth_method' => $this->getSetting('wpAuthMethod', $this->wpAuthMethod),
            'auth_user'   => $this->getSetting('wpAuthUser', $this->wpAuthUser),
            'auth_pass'   => $this->getSetting('wpAuthPassword', $this->wpAuthPassword),
            'jwt_token'   => $this->getSetting('wpJwtToken', $this->wpJwtToken),
            'timeout'     => (int) $this->getSetting('apiTimeout', $this->apiTimeout),
        ];
    }

    /**
     * Construit l'URL complète de l'API WP REST.
     *
     * @param string $path Ex: '/posts', '/categories', '/media/123'
     * @param array $params Paramètres GET
     * @return string
     */
    public function buildWpApiUrl(string $path, array $params = []): string
    {
        $config = $this->getWpApiConfig();
        $url = $config['base_url'] . $config['endpoint'] . $path;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
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
        parent::enable();
    }
}
