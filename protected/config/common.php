<?php

/**
 * Override HumHub / Yii configuration
 * Web + Console
 */

return [


    'language' => 'fr-FR',

    'components' => [

        'user' => [

            // Sync user language ONLY at login
            'on afterLogin' => function ($event) {

                $user = $event->identity;
                if (!$user) {
                    return;
                }


                $currentLanguage = Yii::$app->language;

                // Persist ONLY if different
                if (!empty($currentLanguage) && $user->language !== $currentLanguage) {
                    $user->language = $currentLanguage;
                    $user->save(false, ['language']);
                }
            },
        ],

        'urlManager' => [
            'showScriptName' => false,
            'enablePrettyUrl' => true,
        ],
    ],

    'params' => [
        'hidePoweredBy' => true,
    ],
];
