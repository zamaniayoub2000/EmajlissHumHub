<?php

namespace humhub\modules\customTheme;

use Yii;
use yii\base\Event;
use humhub\modules\customTheme\models\CustomThemeSettings;
use humhub\modules\customTheme\widgets\FooterWidget;

class Events
{
    /**
     * Injection footer + header customization + JS global en fin de body
     * Uniquement sur les pages complètes (pas AJAX, pas Pjax)
     */
    public static function onEndBody($event)
    {
        // Ne rien injecter sur AJAX/Pjax ou si l'utilisateur n'est pas connecté
        if (Yii::$app->request->isAjax || Yii::$app->request->isPjax) {
            return;
        }
        if (Yii::$app->user->isGuest) {
            return;
        }

        // Footer personnalisé
        $footerHtml = CustomThemeSettings::getFooterHtml();
        if (!empty(trim($footerHtml))) {
            echo FooterWidget::widget(['content' => $footerHtml]);
        }

        // Personnalisation du header existant (CSS/JS injecté)
        $headerCustom = CustomThemeSettings::getHeaderCustomization();
        if (!empty(trim($headerCustom))) {
            echo "\n<!-- Custom Theme : Header -->\n";
            echo $headerCustom;
            echo "\n<!-- /Custom Theme : Header -->\n";
        }

        // JS global
        $customJs = CustomThemeSettings::getCustomJs();
        if (!empty(trim($customJs))) {
            echo "<script type=\"text/javascript\">\n";
            echo $customJs . "\n";
            echo "</script>\n";
        }
    }

    /**
     * Injection CSS global avant le rendu
     */
    public static function onBeforeRender($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $view = $event->sender;

        $customCss = CustomThemeSettings::getCustomCss();
        if (!empty(trim($customCss))) {
            $view->registerCss($customCss, ['id' => 'custom-theme-css']);
        }
    }

    /**
     * Lien dans le menu admin
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
