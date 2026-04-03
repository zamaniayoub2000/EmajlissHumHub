<?php

namespace humhub\modules\customTheme\models;

use Yii;
use yii\db\ActiveRecord;
use yii\caching\TagDependency;

/**
 * Modèle ActiveRecord pour la table custom_theme_settings
 *
 * @property int $id
 * @property string $setting_key
 * @property string $setting_value
 * @property bool $is_active
 * @property string $created_at
 * @property string $updated_at
 */
class CustomThemeSettings extends ActiveRecord
{
    const CACHE_KEY = 'custom_theme_settings_cache';
    const CACHE_TAG = 'custom_theme_tag';
    const CACHE_DURATION = 3600;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'custom_theme_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['setting_key'], 'required'],
            [['setting_key'], 'string', 'max' => 100],
            [['setting_value'], 'string'],
            [['is_active'], 'boolean'],
            [['setting_key'], 'unique'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'setting_key' => Yii::t('CustomThemeModule.base', 'Clé'),
            'setting_value' => Yii::t('CustomThemeModule.base', 'Valeur'),
            'is_active' => Yii::t('CustomThemeModule.base', 'Actif'),
            'created_at' => Yii::t('CustomThemeModule.base', 'Créé le'),
            'updated_at' => Yii::t('CustomThemeModule.base', 'Modifié le'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->updated_at = date('Y-m-d H:i:s');
        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        static::invalidateCache();
    }

    /**
     * Récupère une valeur de paramètre par sa clé (avec cache)
     */
    public static function getValue($key, $default = '')
    {
        $settings = static::getAllSettings();
        return isset($settings[$key]) ? $settings[$key]['value'] : $default;
    }

    /**
     * Vérifie si un paramètre est actif
     */
    public static function isActive($key)
    {
        $settings = static::getAllSettings();
        return isset($settings[$key]) ? (bool)$settings[$key]['active'] : false;
    }

    /**
     * Définit une valeur de paramètre
     */
    public static function setValue($key, $value, $isActive = null)
    {
        $model = static::findOne(['setting_key' => $key]);
        if (!$model) {
            $model = new static();
            $model->setting_key = $key;
        }
        $model->setting_value = $value;
        if ($isActive !== null) {
            $model->is_active = $isActive;
        }
        return $model->save();
    }

    /**
     * Active/désactive un paramètre
     */
    public static function toggleActive($key, $active)
    {
        $model = static::findOne(['setting_key' => $key]);
        if ($model) {
            $model->is_active = $active ? 1 : 0;
            return $model->save();
        }
        return false;
    }

    /**
     * Récupère tous les paramètres (avec cache)
     */
    public static function getAllSettings()
    {
        $cache = Yii::$app->cache;
        $settings = $cache->get(static::CACHE_KEY);

        if ($settings === false) {
            $settings = [];
            $rows = static::find()->all();
            foreach ($rows as $row) {
                $settings[$row->setting_key] = [
                    'value' => $row->setting_value,
                    'active' => $row->is_active,
                ];
            }
            $cache->set(
                static::CACHE_KEY,
                $settings,
                static::CACHE_DURATION,
                new TagDependency(['tags' => static::CACHE_TAG])
            );
        }

        return $settings;
    }

    /**
     * Invalide le cache
     */
    public static function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, static::CACHE_TAG);
        Yii::$app->cache->delete(static::CACHE_KEY);
    }

    /**
     * Récupère le footer HTML (depuis DB ou fallback fichier)
     */
    public static function getFooterHtml()
    {
        if (!static::isActive('footer_active')) {
            return '';
        }

        $html = static::getValue('footer_html', '');

        // Fallback vers fichier statique si DB vide
        if (empty(trim($html))) {
            $fallbackFile = Yii::getAlias('@custom-theme/resources/default-footer.html');
            if (file_exists($fallbackFile)) {
                $html = file_get_contents($fallbackFile);
            }
        }

        return $html;
    }

    /**
     * Récupère le header HTML (depuis DB ou fallback fichier)
     */
    public static function getHeaderHtml()
    {
        if (!static::isActive('header_active')) {
            return '';
        }

        $html = static::getValue('header_html', '');

        if (empty(trim($html))) {
            $fallbackFile = Yii::getAlias('@custom-theme/resources/default-header.html');
            if (file_exists($fallbackFile)) {
                $html = file_get_contents($fallbackFile);
            }
        }

        return $html;
    }

    /**
     * Récupère le CSS personnalisé
     */
    public static function getCustomCss()
    {
        if (!static::isActive('css_active')) {
            return '';
        }
        return static::getValue('custom_css', '');
    }

    /**
     * Récupère le JS personnalisé
     */
    public static function getCustomJs()
    {
        if (!static::isActive('js_active')) {
            return '';
        }
        return static::getValue('custom_js', '');
    }
}
