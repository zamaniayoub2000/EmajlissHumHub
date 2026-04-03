<?php

namespace humhub\modules\externalHtmlStream;

use Yii;
use yii\helpers\Url;
use humhub\modules\content\components\ContentContainerModule;

/**
 * Module "Majliss Sync" — External HTML Stream
 *
 * Synchronise les posts publiés depuis la base WordPress Majliss
 * vers le fil d'actualité (stream) d'un espace HumHub.
 *
 * Fonctionnalités :
 *  - Connexion directe à la DB WordPress Majliss
 *  - Récupération des posts publiés avec images
 *  - Création automatique dans le stream HumHub (ContentActiveRecord)
 *  - Suivi des posts déjà synchronisés (anti-doublon)
 *  - Cron automatique via Events HumHub
 *  - Backoffice complet (CRUD + config + logs)
 */
class Module extends ContentContainerModule
{
    /** @inheritdoc */
    public $resourcesPath = 'resources';

    // ── Paramètres connexion Majliss (surchargeables via backoffice) ──

    /** @var string Hôte MySQL Majliss */
    public $majlissDbHost = 'localhost';

    /** @var string Utilisateur MySQL Majliss */
    public $majlissDbUser = '';

    /** @var string Mot de passe MySQL Majliss */
    public $majlissDbPass = '';

    /** @var string Nom de la base Majliss */
    public $majlissDbName = '';

    /** @var string Préfixe des tables WordPress */
    public $majlissDbPrefix = '4aNLlcLvO_';

    /** @var string URL de base pour normaliser les images */
    public $majlissBaseUrl = 'https://intranet.csefrs.ma';

    // ── Paramètres synchronisation ──

    /** @var int Nombre max de posts par sync */
    public $batchLimit = 10;

    /** @var int ID de l'espace HumHub cible */
    public $targetSpaceId = 1;

    /** @var string Image de remplacement si pas de miniature */
    public $fallbackImage = '';

    /** @var bool Activer la synchronisation automatique (cron) */
    public $autoSyncEnabled = true;

    /** @var int Timeout pour le téléchargement des images (secondes) */
    public $imageDownloadTimeout = 20;

    // ── Paramètres sécurité HTML ──

    /** @var bool Activer le cache du contenu */
    public $enableCache = true;

    /** @var int Timeout API en secondes */
    public $apiTimeout = 30;

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
     * Retourne la configuration complète de la connexion Majliss.
     *
     * @return array
     */
    public function getMajlissDbConfig(): array
    {
        return [
            'host'   => $this->getSetting('majlissDbHost', $this->majlissDbHost),
            'user'   => $this->getSetting('majlissDbUser', $this->majlissDbUser),
            'pass'   => $this->getSetting('majlissDbPass', $this->majlissDbPass),
            'name'   => $this->getSetting('majlissDbName', $this->majlissDbName),
            'prefix' => $this->getSetting('majlissDbPrefix', $this->majlissDbPrefix),
        ];
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
