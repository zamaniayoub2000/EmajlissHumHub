<?php

namespace humhub\modules\externalHtmlStream\models;

use Yii;
use yii\base\Model;

/**
 * Formulaire de configuration globale du module.
 *
 * Gère les paramètres de l'API REST WordPress Majliss
 * et les paramètres généraux du module.
 */
class ConfigForm extends Model
{
    // ── API REST WordPress ──
    public $wpApiBaseUrl = 'https://majliscom.csefrs.ma';
    public $wpApiEndpoint = '/wp-json/wp/v2';
    public $wpAuthMethod = 'none';
    public $wpAuthUser = '';
    public $wpAuthPassword = '';
    public $wpJwtToken = '';
    public $wpCategoryFilter = '';
    public $imageBaseUrl = 'https://intranet.csefrs.ma';

    // ── Synchronisation ──
    public $targetSpaceId = 1;
    public $batchLimit = 10;
    public $autoSyncEnabled = true;
    public $fallbackImage = '';
    public $imageDownloadTimeout = 20;

    /**
     * Mapping catégorie WP → espace HumHub.
     * Format JSON : {"Revue de presse": 5, "Actualités": 3}
     * Si une catégorie n'est pas mappée, on utilise targetSpaceId par défaut.
     */
    public $categorySpaceMapping = '';

    // ── Paramètres généraux ──
    public $enableCache = true;
    public $apiTimeout = 30;
    public $whitelistedDomains = '';
    public $allowIframes = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // API WordPress
            [['wpApiBaseUrl'], 'required'],
            [['wpApiBaseUrl'], 'url', 'defaultScheme' => 'https'],
            [['wpApiEndpoint'], 'string', 'max' => 255],
            [['wpAuthMethod'], 'in', 'range' => ['none', 'basic', 'jwt', 'application_password']],
            [['wpAuthUser', 'wpAuthPassword', 'wpJwtToken'], 'string', 'max' => 500],
            [['wpCategoryFilter'], 'string', 'max' => 255],
            [['wpCategoryFilter'], 'match', 'pattern' => '/^[\d,\s]*$/',
                'message' => 'Entrez des IDs numériques séparés par des virgules (ex: 1,5,12)'],
            [['imageBaseUrl'], 'url', 'defaultScheme' => 'https'],

            // Synchronisation
            [['targetSpaceId', 'batchLimit', 'imageDownloadTimeout'], 'integer', 'min' => 1],
            [['batchLimit'], 'integer', 'max' => 100],
            [['autoSyncEnabled', 'enableCache', 'allowIframes'], 'boolean'],
            [['fallbackImage'], 'string', 'max' => 1024],
            [['categorySpaceMapping'], 'string', 'max' => 5000],
            [['categorySpaceMapping'], 'validateJsonMapping'],

            // Paramètres généraux
            [['apiTimeout'], 'integer', 'min' => 5, 'max' => 120],
            [['whitelistedDomains'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'wpApiBaseUrl'        => Yii::t('ExternalHtmlStreamModule.base', 'URL du site WordPress'),
            'wpApiEndpoint'       => Yii::t('ExternalHtmlStreamModule.base', 'Endpoint API REST'),
            'wpAuthMethod'        => Yii::t('ExternalHtmlStreamModule.base', 'Méthode d\'authentification'),
            'wpAuthUser'          => Yii::t('ExternalHtmlStreamModule.base', 'Utilisateur API'),
            'wpAuthPassword'      => Yii::t('ExternalHtmlStreamModule.base', 'Mot de passe / Application Password'),
            'wpJwtToken'          => Yii::t('ExternalHtmlStreamModule.base', 'Token JWT'),
            'wpCategoryFilter'    => Yii::t('ExternalHtmlStreamModule.base', 'Filtrer par catégories (IDs)'),
            'imageBaseUrl'        => Yii::t('ExternalHtmlStreamModule.base', 'URL de base pour les images'),
            'targetSpaceId'       => Yii::t('ExternalHtmlStreamModule.base', 'Espace HumHub cible'),
            'batchLimit'          => Yii::t('ExternalHtmlStreamModule.base', 'Posts par synchronisation'),
            'autoSyncEnabled'     => Yii::t('ExternalHtmlStreamModule.base', 'Synchronisation automatique'),
            'fallbackImage'       => Yii::t('ExternalHtmlStreamModule.base', 'Image par défaut'),
            'imageDownloadTimeout' => Yii::t('ExternalHtmlStreamModule.base', 'Timeout téléchargement images (s)'),
            'categorySpaceMapping' => Yii::t('ExternalHtmlStreamModule.base', 'Mapping catégorie → espace'),
            'enableCache'         => Yii::t('ExternalHtmlStreamModule.base', 'Activer le cache'),
            'apiTimeout'          => Yii::t('ExternalHtmlStreamModule.base', 'Timeout API (secondes)'),
            'whitelistedDomains'  => Yii::t('ExternalHtmlStreamModule.base', 'Domaines autorisés'),
            'allowIframes'        => Yii::t('ExternalHtmlStreamModule.base', 'Autoriser les iframes'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'wpApiBaseUrl'     => 'URL de votre site WordPress Majliss. Ex: https://majliscom.csefrs.ma',
            'wpApiEndpoint'    => 'Par défaut /wp-json/wp/v2. Ne changez que si endpoint personnalisé.',
            'wpAuthMethod'     => '"none" si l\'API est publique. "basic" ou "application_password" si authentification requise.',
            'wpAuthUser'       => 'Nom d\'utilisateur WordPress (uniquement si authentification activée).',
            'wpAuthPassword'   => 'Application Password WordPress (Utilisateurs > Profil > Mots de passe d\'application).',
            'wpJwtToken'       => 'Token JWT si vous utilisez le plugin JWT Authentication.',
            'wpCategoryFilter' => 'IDs de catégories WP à synchroniser (vide = toutes). Ex: 3,7,12',
            'imageBaseUrl'     => 'URL utilisée pour réécrire les chemins d\'images dans le contenu.',
            'targetSpaceId'    => 'ID de l\'espace HumHub où les posts seront publiés.',
            'batchLimit'       => 'Nombre maximum de posts récupérés par appel API.',
            'whitelistedDomains' => 'Laissez vide pour autoriser tous les domaines.',
            'allowIframes'     => 'Attention : les iframes peuvent présenter des risques de sécurité.',
            'fallbackImage'    => 'URL d\'une image affichée si un post n\'a pas de miniature.',
            'categorySpaceMapping' => 'Format JSON. Ex: {"Revue de presse": 5, "Actualités": 3}. La clé est le nom de la catégorie WP, la valeur est l\'ID de l\'espace HumHub.',
        ];
    }

    /**
     * Valide que le mapping catégorie → espace est un JSON valide.
     */
    public function validateJsonMapping($attribute, $params): void
    {
        if (empty($this->$attribute)) {
            return;
        }
        $decoded = json_decode($this->$attribute, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addError($attribute, 'Le mapping doit être un JSON valide. Ex: {"Revue de presse": 5}');
        }
    }

    /**
     * Charge les valeurs depuis les settings du module.
     */
    public function loadSettings(): void
    {
        $module = Yii::$app->getModule('external-html-stream');

        foreach ($this->attributes() as $attr) {
            $value = $module->settings->get($attr);
            if ($value !== null && $value !== '') {
                if (in_array($attr, ['autoSyncEnabled', 'enableCache', 'allowIframes'])) {
                    $this->$attr = (bool) $value;
                } elseif (in_array($attr, ['targetSpaceId', 'batchLimit', 'apiTimeout', 'imageDownloadTimeout'])) {
                    $this->$attr = (int) $value;
                } else {
                    $this->$attr = $value;
                }
            } else {
                if (property_exists($module, $attr)) {
                    $this->$attr = $module->$attr;
                }
            }
        }
    }

    /**
     * Sauvegarde les valeurs dans les settings du module.
     *
     * @return bool
     */
    public function saveSettings(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $module = Yii::$app->getModule('external-html-stream');

        foreach ($this->attributes() as $attr) {
            $value = $this->$attr;

            if (is_bool($value)) {
                $value = $value ? 1 : 0;
            }

            $module->settings->set($attr, $value);
        }

        return true;
    }
}
