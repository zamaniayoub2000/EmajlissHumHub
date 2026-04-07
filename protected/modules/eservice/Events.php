<?php

namespace humhub\modules\eservice;

use Yii;
use yii\helpers\Url;

class Events
{
    /**
     * Adds the E-Services menu item to the top navigation.
     *
     * @param \yii\base\Event $event
     */
    public static function onTopMenuInit($event)
    {
        $event->sender->addItem([
            'label' => Yii::t('EserviceModule.base', 'E-Services'),
            'icon' => '<i class="fa fa-cogs"></i>',
            'url' => Url::to(['/eservice/index/index']),
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id === 'eservice'),
        ]);
    }
}
