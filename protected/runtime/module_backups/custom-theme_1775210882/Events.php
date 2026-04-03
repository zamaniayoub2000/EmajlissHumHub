<?php

namespace humhub\modules\customTheme;

use Yii;
use yii\base\Event;
use humhub\modules\customTheme\models\CustomThemeSettings;
use humhub\modules\customTheme\widgets\FooterWidget;
use humhub\modules\customTheme\widgets\HeaderWidget;

/**
 * Gestionnaire d'événements du module Custom Theme
 * Injecte le header, footer, CSS et JS dans toutes les pages
 */
class Events
{
    /**
     * Injection du footer personnalisé en fin de body
     */
    public static function onEndBody($event)
    {
        $view = $event->sender;

        // Injecter le footer
        $footerHtml = CustomThemeSettings::getFooterHtml();
        if (!empty($footerHtml)) {
            echo FooterWidget::widget(['content' => $footerHtml]);
        }

        // Injecter le JS personnalisé
        $customJs = CustomThemeSettings::getCustomJs();
        if (!empty(trim($customJs))) {
            echo '<script type="text/javascript">' . "\n";
            echo '/* Custom Theme JS */' . "\n";
            echo $customJs . "\n";
            echo '</script>' . "\n";
        }
    }

    /**
     * Injection du header personnalisé en début de body
     */
    public static function onBeginBody($event)
    {
        $headerHtml = CustomThemeSettings::getHeaderHtml();
        if (!empty($headerHtml)) {
            echo HeaderWidget::widget(['content' => $headerHtml]);
        }
    }

    /**
     * Injection du CSS personnalisé avant le rendu
     */
    public static function onBeforeRender($event)
    {
        $view = $event->sender;

        $customCss = CustomThemeSettings::getCustomCss();
        if (!empty(trim($customCss))) {
            $view->registerCss($customCss, ['id' => 'custom-theme-css']);
        }
    }

    /**
     * Ajout d'un lien dans le menu d'administration
     */
    public static function onAdminMenuInit($event)
    {
        $event->sender->addItem([
            'label' => Yii::t('CustomThemeModule.base', 'Custom Theme'),
            'url' => ['/custom-theme/admin/index'],
            'group' => 'settings',
            'icon' => '<i class="fa fa-paint-brush"></i>',
            'isActive' => (
                Yii::$app->controller->module &&
                Yii::$app->controller->module->id === 'custom-theme'
            ),
            'sortOrder' => 650,
        ]);
    }
}
