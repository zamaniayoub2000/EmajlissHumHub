<?php

namespace humhub\modules\customTheme\models;

use Yii;
use yii\base\Model;

/**
 * Formulaire d'administration du thème custom
 * Sert d'intermédiaire entre le formulaire admin et le modèle ActiveRecord
 */
class CustomThemeForm extends Model
{
    public $footer_html;
    public $header_html;
    public $custom_css;
    public $custom_js;
    public $footer_active;
    public $header_active;
    public $css_active;
    public $js_active;
    public $sanitize_html;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['footer_html', 'header_html', 'custom_css', 'custom_js'], 'string'],
            [['footer_active', 'header_active', 'css_active', 'js_active', 'sanitize_html'], 'boolean'],
            [['footer_active', 'header_active', 'css_active', 'js_active', 'sanitize_html'], 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'footer_html' => Yii::t('CustomThemeModule.base', 'Footer HTML'),
            'header_html' => Yii::t('CustomThemeModule.base', 'Header HTML'),
            'custom_css' => Yii::t('CustomThemeModule.base', 'CSS personnalisé'),
            'custom_js' => Yii::t('CustomThemeModule.base', 'JavaScript personnalisé'),
            'footer_active' => Yii::t('CustomThemeModule.base', 'Activer le footer personnalisé'),
            'header_active' => Yii::t('CustomThemeModule.base', 'Activer le header personnalisé'),
            'css_active' => Yii::t('CustomThemeModule.base', 'Activer le CSS personnalisé'),
            'js_active' => Yii::t('CustomThemeModule.base', 'Activer le JS personnalisé'),
            'sanitize_html' => Yii::t('CustomThemeModule.base', 'Activer la sanitization HTML'),
        ];
    }

    /**
     * Charge les valeurs depuis la base de données
     */
    public function loadFromDb()
    {
        $this->footer_html = CustomThemeSettings::getValue('footer_html', '');
        $this->header_html = CustomThemeSettings::getValue('header_html', '');
        $this->custom_css = CustomThemeSettings::getValue('custom_css', '');
        $this->custom_js = CustomThemeSettings::getValue('custom_js', '');
        $this->footer_active = CustomThemeSettings::getValue('footer_active', '1');
        $this->header_active = CustomThemeSettings::getValue('header_active', '0');
        $this->css_active = CustomThemeSettings::getValue('css_active', '1');
        $this->js_active = CustomThemeSettings::getValue('js_active', '1');
        $this->sanitize_html = CustomThemeSettings::getValue('sanitize_html', '0');
    }

    /**
     * Sauvegarde les valeurs dans la base de données
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $success = true;
        $success = $success && CustomThemeSettings::setValue('footer_html', $this->footer_html);
        $success = $success && CustomThemeSettings::setValue('header_html', $this->header_html);
        $success = $success && CustomThemeSettings::setValue('custom_css', $this->custom_css);
        $success = $success && CustomThemeSettings::setValue('custom_js', $this->custom_js);
        $success = $success && CustomThemeSettings::setValue('footer_active', $this->footer_active ? '1' : '0');
        $success = $success && CustomThemeSettings::setValue('header_active', $this->header_active ? '1' : '0');
        $success = $success && CustomThemeSettings::setValue('css_active', $this->css_active ? '1' : '0');
        $success = $success && CustomThemeSettings::setValue('js_active', $this->js_active ? '1' : '0');
        $success = $success && CustomThemeSettings::setValue('sanitize_html', $this->sanitize_html ? '1' : '0');

        if ($success) {
            CustomThemeSettings::invalidateCache();
        }

        return $success;
    }
}
