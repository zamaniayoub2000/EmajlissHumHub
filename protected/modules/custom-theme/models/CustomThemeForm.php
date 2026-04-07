<?php

namespace humhub\modules\customTheme\models;

use Yii;
use yii\base\Model;

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

    public function rules()
    {
        return [
            [['footer_html', 'header_html', 'custom_css', 'custom_js'], 'string'],
            [['footer_active', 'header_active', 'css_active', 'js_active', 'sanitize_html'], 'boolean'],
            [['footer_active', 'header_active', 'css_active', 'js_active', 'sanitize_html'], 'default', 'value' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'footer_html' => 'Footer HTML',
            'header_html' => 'Header (CSS/JS)',
            'custom_css' => 'CSS',
            'custom_js' => 'JavaScript',
            'footer_active' => 'Footer actif',
            'header_active' => 'Header actif',
            'css_active' => 'CSS actif',
            'js_active' => 'JS actif',
            'sanitize_html' => 'Sanitization HTML',
        ];
    }

    public function loadFromDb()
    {
        $this->footer_html = CustomThemeSettings::getValue('footer_html', '');
        $this->header_html = CustomThemeSettings::getValue('header_html', '');
        $this->custom_css = CustomThemeSettings::getValue('custom_css', '');
        $this->custom_js = CustomThemeSettings::getValue('custom_js', '');
        $this->footer_active = CustomThemeSettings::getValue('footer_active', '0');
        $this->header_active = CustomThemeSettings::getValue('header_active', '0');
        $this->css_active = CustomThemeSettings::getValue('css_active', '0');
        $this->js_active = CustomThemeSettings::getValue('js_active', '0');
        $this->sanitize_html = CustomThemeSettings::getValue('sanitize_html', '0');
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $ok = true;
        $ok = $ok && CustomThemeSettings::setValue('footer_html', $this->footer_html);
        $ok = $ok && CustomThemeSettings::setValue('header_html', $this->header_html);
        $ok = $ok && CustomThemeSettings::setValue('custom_css', $this->custom_css);
        $ok = $ok && CustomThemeSettings::setValue('custom_js', $this->custom_js);
        $ok = $ok && CustomThemeSettings::setValue('footer_active', $this->footer_active ? '1' : '0');
        $ok = $ok && CustomThemeSettings::setValue('header_active', $this->header_active ? '1' : '0');
        $ok = $ok && CustomThemeSettings::setValue('css_active', $this->css_active ? '1' : '0');
        $ok = $ok && CustomThemeSettings::setValue('js_active', $this->js_active ? '1' : '0');
        $ok = $ok && CustomThemeSettings::setValue('sanitize_html', $this->sanitize_html ? '1' : '0');

        if ($ok) {
            CustomThemeSettings::invalidateCache();
        }

        return $ok;
    }
}
