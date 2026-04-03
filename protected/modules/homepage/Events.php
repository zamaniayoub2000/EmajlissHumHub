<?php

/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\homepage;

use humhub\helpers\ControllerHelper;
use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\homepage\models\Homepage;
use humhub\modules\homepage\permissions\ManageHomepage;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\user\models\GroupUser;
use Yii;
use yii\base\ActionEvent;
use yii\base\Event;
use yii\base\InvalidRouteException;
use yii\db\AfterSaveEvent;
use yii\helpers\Url;
use yii\web\UserEvent;

class Events
{
    public static function onAdminMenuInit($event): void
    {
        /** @var AdminMenu $menu */
        $menu = $event->sender;

        /** @var Module $module */
        $module = Yii::$app->getModule('homepage');

        if (Yii::$app->user->can(ManageHomepage::class)) { // Don't move in 'isVisible' as it doesn't work in all cases and because the "if" costs less
            $menu->addEntry(new MenuLink([
                'label' => Yii::t('HomepageModule.base', 'Homepages'),
                'icon' => $module->icon,
                'sortOrder' => 310,
                'isActive' => ControllerHelper::isActivePath('homepage', 'admin'),
                'url' => Url::to(['/homepage/admin/index']),
                'isVisible' => true,
            ]));
        }
    }

    /**
     * After login, redirect to main group home page
     * @param $event
     * @return void
     * @throws InvalidRouteException
     */
    public static function onAfterLogin($event): void
    {
        if (
            Yii::$app->user->identity
            && Yii::$app->user->getReturnUrl() === Yii::$app->homeUrl // Don't redirect home if coming from a page that requires to be logged in (as in this case, the returnUrl will be different from the home page)
        ) {
            $homepageUrl = Homepage::getUrlForUser();
            if ($homepageUrl) {
                Yii::$app->response->redirect($homepageUrl)->send();
            }
        }
    }

    /**
     * After logout, set homepage to main home page
     * @param $event
     * @return void
     */
    public static function onAfterLogout($event): void
    {
        if (Yii::$app->user->identity) {
            $homepageUrl = Homepage::getUrlForGuest();
            if ($homepageUrl) {
                Yii::$app->homeUrl = $homepageUrl;
            }
        }
    }

    public static function onApplicationBeforeAction(ActionEvent $event): void
    {
        if (
            Yii::$app->request->isConsoleRequest
            || !Yii::$app->db->schema->getTableSchema('homepage') // `homepage` table doesn't exist (e.g., just after enabling the module, the migration is not yet done)
        ) {
            return;
        }

        $homepageUrl = Yii::$app->user->isGuest
            ? Homepage::getUrlForGuest()
            : Homepage::getUrlForUser();

        if ($homepageUrl) {
            if (Yii::$app->homeUrl !== $homepageUrl) {
                Yii::$app->homeUrl = $homepageUrl;
            }
            if (
                Yii::$app->user->loginUrl !== $homepageUrl
                && Yii::$app->user->isGuest
            ) {
                /* @var $userModule Module */
                $userModule = Yii::$app->getModule('user');
                if (!$userModule->settings->get('auth.allowGuestAccess')) {
                    Yii::$app->user->loginUrl = $homepageUrl;
                }
            }
        }
    }

    public static function onGroupUserAfterInsert(AfterSaveEvent $event): void
    {
        /** @var GroupUser $groupUser */
        $groupUser = $event->sender;
        Yii::$app->cache->delete(Homepage::getCacheId($groupUser->user_id ?? null));
    }

    public static function onGroupUserAfterDelete(Event $event): void
    {
        /** @var GroupUser $groupUser */
        $groupUser = $event->sender;
        Yii::$app->cache->delete(Homepage::getCacheId($groupUser->user_id ?? null));
    }

    public static function onGroupAfterDelete(Event $event): void
    {
        Homepage::deleteAllCache();
    }

    public static function onRegistrationAfterRegistration(UserEvent $event): void
    {
        Yii::$app->cache->delete(Homepage::getCacheId($event->identity->id ?? null));
    }
}
