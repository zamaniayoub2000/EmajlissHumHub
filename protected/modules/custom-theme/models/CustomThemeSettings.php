<?php

namespace humhub\modules\customTheme\models;

use Yii;
use yii\db\ActiveRecord;
use yii\caching\TagDependency;

/**
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

    public static function tableName()
    {
        return 'custom_theme_settings';
    }

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

    public function beforeSave($insert)
    {
        $this->updated_at = date('Y-m-d H:i:s');
        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        static::invalidateCache();
    }

    public static function getValue($key, $default = '')
    {
        $settings = static::getAllSettings();
        return isset($settings[$key]) ? $settings[$key]['value'] : $default;
    }

    public static function isActive($key)
    {
        $settings = static::getAllSettings();
        return isset($settings[$key]) ? (bool)$settings[$key]['active'] : false;
    }

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

    public static function invalidateCache()
    {
        TagDependency::invalidate(Yii::$app->cache, static::CACHE_TAG);
        Yii::$app->cache->delete(static::CACHE_KEY);
    }

    public static function getFooterHtml()
    {
        if (!static::isActive('footer_active')) {
            return '';
        }
        return static::getValue('footer_html', '');
    }

    public static function getHeaderCustomization()
    {
        if (!static::isActive('header_active')) {
            return '';
        }
        return static::getValue('header_html', '');
    }

    public static function getCustomCss()
    {
        if (!static::isActive('css_active')) {
            return '';
        }
        return static::getValue('custom_css', '');
    }

    public static function getCustomJs()
    {
        if (!static::isActive('js_active')) {
            return '';
        }
        return static::getValue('custom_js', '');
    }
}
