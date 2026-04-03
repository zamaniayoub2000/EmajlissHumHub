<?php

/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\components\Application;
use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\homepage\Events;
use humhub\modules\user\controllers\AuthController;
use humhub\modules\user\models\forms\Registration;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;
use yii\web\User;

/** @noinspection MissedFieldInspection */
return [
    'id' => 'homepage',
    'class' => humhub\modules\homepage\Module::class,
    'namespace' => 'humhub\modules\homepage',
    'events' => [
        [
            'class' => AdminMenu::class,
            'event' => AdminMenu::EVENT_INIT,
            'callback' => [Events::class, 'onAdminMenuInit'],
        ],
        [
            'class' => AuthController::class,
            'event' => AuthController::EVENT_AFTER_LOGIN,
            'callback' => [Events::class, 'onAfterLogin'],
        ],
        [
            'class' => Application::class,
            'event' => Application::EVENT_BEFORE_ACTION,
            'callback' => [Events::class, 'onApplicationBeforeAction'],
        ],
        [
            'class' => User::class,
            'event' => User::EVENT_AFTER_LOGOUT,
            'callback' => [Events::class, 'onAfterLogout'],
        ],
        [
            'class' => GroupUser::class,
            'event' => GroupUser::EVENT_AFTER_INSERT,
            'callback' => [Events::class, 'onGroupUserAfterInsert'],
        ],
        [
            'class' => Group::class,
            'event' => Group::EVENT_AFTER_DELETE,
            'callback' => [Events::class, 'onGroupAfterDelete'],
        ],
        [
            'class' => GroupUser::class,
            'event' => GroupUser::EVENT_AFTER_DELETE,
            'callback' => [Events::class, 'onGroupUserAfterDelete'],
        ],
        [
            'class' => Registration::class,
            'event' => Registration::EVENT_AFTER_REGISTRATION,
            'callback' => [Events::class, 'onRegistrationAfterRegistration'],
        ],
    ],
    'urlManagerRules' => [
        'home' => 'homepage/index',
    ],
];
