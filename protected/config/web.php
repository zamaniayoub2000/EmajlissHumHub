<?php
/**
 * This file provides to overwrite the default HumHub / Yii configuration by your local Web environments
 * @see http://www.yiiframework.com/doc-2.0/guide-concept-configurations.html
 * @see https://docs.humhub.org/docs/admin/advanced-configuration
 */
return [
    'modules' => [
        'user' => [
            'class' => 'humhub\modules\user\Module',

            'profileDefaultRoute' => '/user/profile/about',
        ],
    ],
];


return [
    'modules' => [
        'web' => [
            'security' =>  [
                "csp" => [
                    "font-src" => [
                        "self" => true,
                        "allow" => [
                            "https://fonts.google.com",
                        ],
                    ],
                ],
            ],
        ],
    ],
];