<?php

namespace humhub\modules\externalHtmlStream\models;

use Yii;
use yii\base\Model;

/**
 * Formulaire de configuration globale du module.
 *
 * Gère à la fois les paramètres de connexion Majliss
 * et les paramètres généraux du module.
 */
class ConfigForm extends Model
{
    // ── Connexion Majliss ──
    public $majlissDbHost = 'localhost';
    public $majlissDbUser = '';
    public $majlissDbPass = '';
    public $majlissDbName = '';
    public $majlissDbPrefix = '4aNLlcLvO_';
    public $majlissBaseUrl = 'https://intranet.csefrs.ma';

    // ── Synchronisation ──
    public $targetSpaceId = 1;
    public $batchLimit = 10;
    public $autoSyncEnabled = true;
    public $fallbackImage = '';
    public $imageDownloadTimeout = 20;

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
            // Connexion Majliss
            [['majlissDbHost', 'majlissDbUser', 'majlissDbName'], 'required'],
            [['majlissDbHost', 'majlissDbUser', 'majlissDbPass', 'majlissDbName', 'majlissDbPrefix'], 'string', 'max' => 255],
            [['majlissBaseUrl'], 'url', 'defaultScheme' => 'https'],

            // Synchronisation
            [['targetSpaceId', 'batchLimit', 'imageDownloadTimeout'], 'integer', 'min' => 1],
            [['batchLimit'], 'integer', 'max' => 100],
            [['autoSyncEnabled', 'enableCache', 'allowIframes'], 'boolean'],
            [['fallbackImage'], 'string', 'max' => 1024],

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
            'majlissDbHost'       => Yii::t('ExternalHtmlStreamModule.base', 'Hôte MySQL Majliss'),
            'majlissDbUser'       => Yii::t('ExternalHtmlStreamModule.base', 'Utilisateur MySQL'),
            'majlissDbPass'       => Yii::t('ExternalHtmlStreamModule.base', 'Mot de passe MySQL'),
            'majlissDbName'       => Yii::t('ExternalHtmlStreamModule.base', 'Nom de la base'),
            'majlissDbPrefix'     => Yii::t('ExternalHtmlStreamModule.base', 'Préfixe des tables WP'),
            'majlissBaseUrl'      => Yii::t('ExternalHtmlStreamModule.base', 'URL de base Majliss'),
            'targetSpaceId'       => Yii::t('ExternalHtmlStreamModule.base', 'Espace HumHub cible'),
            'batchLimit'          => Yii::t('ExternalHtmlStreamModule.base', 'Posts par synchronisation'),
            'autoSyncEnabled'     => Yii::t('ExternalHtmlStreamModule.base', 'Synchronisation automatique'),
            'fallbackImage'       => Yii::t('ExternalHtmlStreamModule.base', 'Image par défaut'),
            'imageDownloadTimeout' => Yii::t('ExternalHtmlStreamModule.base', 'Timeout téléchargement images (s)'),
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
            'majlissDbHost'      => 'Généralement "localhost" si HumHub et Majliss sont sur le même serveur.',
            'majlissDbPrefix'    => 'Préfixe des tables WordPress, ex: wp_ ou 4aNLlcLvO_',
            'majlissBaseUrl'     => 'URL utilisée pour normaliser les chemins des images.',
            'targetSpaceId'      => 'ID de l\'espace HumHub où les posts seront publiés.',
            'batchLimit'         => 'Nombre maximum de posts traités par exécution du cron.',
            'whitelistedDomains' => 'Laissez vide pour autoriser tous les domaines. Ex: api.exemple.com, cdn.exemple.com',
            'allowIframes'       => 'Attention : les iframes peuvent présenter des risques de sécurité.',
            'fallbackImage'      => 'URL d\'une image affichée si un post n\'a pas de miniature.',
        ];
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
                // Convertir les booléens
                if (in_array($attr, ['autoSyncEnabled', 'enableCache', 'allowIframes'])) {
                    $this->$attr = (bool) $value;
                } elseif (in_array($attr, ['targetSpaceId', 'batchLimit', 'apiTimeout', 'imageDownloadTimeout'])) {
                    $this->$attr = (int) $value;
                } else {
                    $this->$attr = $value;
                }
            } else {
                // Fallback sur la propriété du module
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

            // Convertir les booléens en int pour le stockage
            if (is_bool($value)) {
                $value = $value ? 1 : 0;
            }

            $module->settings->set($attr, $value);
        }

        return true;
    }
}
