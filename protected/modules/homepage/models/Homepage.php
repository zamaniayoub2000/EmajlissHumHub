<?php

/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\homepage\models;

use humhub\components\ActiveRecord;
use humhub\helpers\Html;
use humhub\modules\activity\widgets\ActivityStreamViewer;
use humhub\modules\birthday\widgets\BirthdaySidebarWidget;
use humhub\modules\calendar\widgets\UpcomingEvents;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\custom_pages\helpers\PageType;
use humhub\modules\custom_pages\interfaces\CustomPagesService;
use humhub\modules\custom_pages\models\CustomPage;
use humhub\modules\custom_pages\modules\template\helpers\PagePermissionHelper;
use humhub\modules\custom_pages\widgets\SnippetWidget;
use humhub\modules\dashboard\widgets\DashboardContent;
use humhub\modules\homepage\helpers\StringHelper;
use humhub\modules\homepage\jobs\DeleteAllUsersCache;
use humhub\modules\homepage\Module;
use humhub\modules\mostactiveusers\widgets\Sidebar;
use humhub\modules\newmembers\widgets\NewMembersSidebarWidget;
use humhub\modules\onlineUsers\widgets\SidebarWidget;
use humhub\modules\show_content\widgets\SelectedContents;
use humhub\modules\space\widgets\NewSpaces;
use humhub\modules\tasks\widgets\MyTasks;
use humhub\modules\tour\widgets\Dashboard as TourDashboard;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\User;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\FooterMenu;
use Throwable;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\IdentityInterface;

/**
 * @property $id int
 * @property $enabled int|bool
 * @property $target string
 * @property $group_id int
 * @property $group_priority_order int
 * @property $title string
 * @property $content string
 * @property $content_type string
 * @property $widgets string
 * @property $no_frame int|bool
 * @property $layout string
 *
 * @property-read Group $group
 * @property-read array $contentTypeOptions
 * @property-read array $targetOptions
 * @property-read string $targetName
 * @property-read array $sidebarWidgets
 * @property-read null|string $url
 * @property-read string $contentView
 * @property-read array $widgetItems
 */
class Homepage extends ActiveRecord
{
    public const TARGET_GUEST = 'guest';
    public const TARGET_REGISTERED = 'registered';
    public const TARGET_GROUP = 'group';

    public const CONTENT_TYPE_RICH_TEXT = 'rich_text';
    public const CONTENT_TYPE_HTML = 'html';
    public const CONTENT_TYPE_IFRAME = 'iframe';
    public const CONTENT_TYPE_URL = 'url';
    public const CONTENT_TYPE_DEFAULT = self::CONTENT_TYPE_RICH_TEXT;

    public const WIDGET_DASHBOARD_STREAM = DashboardContent::class;
    public const WIDGET_TOUR = TourDashboard::class;
    public const WIDGET_CUSTOM_PAGES = SnippetWidget::class;
    public const WIDGET_SELECTED_CONTENT = SelectedContents::class;
    public const WIDGET_ACTIVITY_STREAM = ActivityStreamViewer::class;
    public const WIDGET_UPCOMING_EVENTS = UpcomingEvents::class;
    public const WIDGET_NEW_MEMBERS = NewMembersSidebarWidget::class;
    public const WIDGET_MOST_ACTIVE_USERS = Sidebar::class;
    public const WIDGET_ONLINE_USERS = SidebarWidget::class;
    public const WIDGET_NEW_SPACES = NewSpaces::class;
    public const WIDGET_MY_TASKS = MyTasks::class;
    public const WIDGET_BIRTHDAY = BirthdaySidebarWidget::class;
    public const WIDGET_FOOTER = FooterMenu::class;

    public const LAYOUT_CL_DL_SR = 'content_left_dashboard_left_sidebar_right';
    public const LAYOUT_SL_CR_DR = 'sidebar_left_content_right_dashboard_right';
    public const LAYOUT_CT_DL_SR = 'content_top_dashboard_left_sidebar_right';
    public const LAYOUT_CT_SL_DR = 'content_top_sidebar_left_dashboard_right';
    public const LAYOUT_DEFAULT = self::LAYOUT_CL_DL_SR;

    public const CACHE_KEY_PREFIX = 'homepage_';

    public static function enabledLabel(string $target, ?int $groupId = null): string
    {
        return static::getQuery($target, $groupId, true)->exists()
            ? Badge::success(Yii::t('HomepageModule.admin', 'Enabled'))
            : Badge::danger(Yii::t('HomepageModule.admin', 'Disabled'));
    }

    public static function getQuery(string $target, int|array|null $groupId = null, bool $filterIsEnabled = false): ActiveQuery
    {
        $query = self::find()
            ->where(['target' => $target]);
        if ($filterIsEnabled) {
            $query->andWhere(['enabled' => true]);
        }
        if ($groupId) {
            $query->andWhere(['group_id' => $groupId])
                ->orderBy(['group_priority_order' => SORT_ASC]);
        }
        return $query;
    }

    public static function getContentViewForUser(string $target, User|IdentityInterface|null $user = null): ?string
    {
        return static::findOne(static::getIdForUser($target, $user))?->getContentView();
    }

    public function getContentView(): string
    {
        return match ($this->content_type) {
            self::CONTENT_TYPE_RICH_TEXT => RichText::output(StringHelper::replaceTags((string)$this->content)),
            // Add nonce to scripts and replace bootstrap 3 class img-responsive (added by TinyMCE) by bootstrap 5 class img-fluid
            self::CONTENT_TYPE_HTML => str_ireplace(
                ['<script>', 'img-responsive'],
                ['<script ' . Html::nonce() . '>', 'img-fluid'],
                StringHelper::replaceTags($this->content, true),
            ),
            self::CONTENT_TYPE_IFRAME => Html::tag('iframe', '', [
                'src' => (string)$this->content,
                'style' => 'border: none; width:100%; height: 100%; min-height: 400px;',
                'allowfullscreen' => '',
            ]),
            // URL type cannot be returned as a string
            default => '',
        };
    }

    public static function getUrlForGuest(): ?string
    {
        return Yii::$app->cache->getOrSet(
            static::getCacheId(null),
            function () {
                return static::getForGuest()?->getUrl();
            },
        );
    }

    public static function getCacheId(?int $userId): string
    {
        return
            self::CACHE_KEY_PREFIX
            . Url::home() . '_' // allows changing the cache ID if the base URL is different or if switching pretty URL
            . ($userId ? (string)$userId : 'guest');
    }

    public function getUrl(): ?string
    {
        if (!$this) {
            return null;
        }
        return $this->content_type === self::CONTENT_TYPE_URL
            ? $this->content
            : Url::to(['/homepage/index']);
    }

    public static function getForGuest(): ?static
    {
        return static::getQuery(self::TARGET_GUEST, null, true)->one();
    }

    public static function getUrlForUser(User|IdentityInterface|null $user = null): ?string
    {
        $user = $user ?? Yii::$app->user->identity;
        if ($user === null) {
            return null;
        }

        return Yii::$app->cache->getOrSet(
            static::getCacheId($user->id),
            function () use ($user) {
                return static::getForUser($user)?->getUrl();
            },
        );
    }

    public static function getForUser(User|IdentityInterface|null $user = null)
    {
        $user = $user ?? Yii::$app->user->identity;
        if ($user === null) {
            return null;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('homepage');

        // Homepage for user group
        if ($module->configuration->groupHomepages) {
            $groupHomepage = static::getQuery(self::TARGET_GROUP, $user->getGroups()->column(), true)->one();
            if ($groupHomepage) {
                return $groupHomepage;
            }
        }

        // Homepage for registered user
        return static::getQuery(self::TARGET_REGISTERED, null, true)->one();
    }

    public static function tableName()
    {
        return 'homepage';
    }

    /**
     * @inheridoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        static::deleteAllCache();
    }

    public static function deleteAllCache(): void
    {
        // Delete cache for guests
        Yii::$app->cache->delete(static::getCacheId(null));
        // Delete current user cache to display the new homepage immediately for him
        if (Yii::$app->user->identity) {
            Yii::$app->cache->delete(static::getCacheId(Yii::$app->user->id));
        }
        // Delete cache of all other users
        Yii::$app->queue->push(new DeleteAllUsersCache());
    }

    public function getGroup(): ActiveQuery
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    /**
     * @inheridoc
     */
    public function rules()
    {
        return [
            [['target', 'content_type'], 'required'],
            [['enabled', 'no_frame'], 'boolean'],
            [['target', 'layout'], 'string', 'max' => 100],
            [['title'], 'string', 'max' => 255],
            [['content', 'content_type', 'widgets'], 'string'],
            [['group_id', 'group_priority_order'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'enabled' => Yii::t('HomepageModule.admin', 'Enabled'),
            'target' => Yii::t('HomepageModule.admin', 'Target'),
            'title' => Yii::t('HomepageModule.admin', 'Title'),
            'content' => Yii::t('HomepageModule.admin', 'Content'),
            'content_type' => Yii::t('HomepageModule.admin', 'Content Type'),
            'widgets' => Yii::t('HomepageModule.admin', 'Widgets'),
            'no_frame' => Yii::t('HomepageModule.admin', 'No frame around the content'),
            'layout' => Yii::t('HomepageModule.admin', 'Layout'),
            'group_id' => Yii::t('HomepageModule.admin', 'Group'),
            'group_priority_order' => Yii::t('HomepageModule.admin', 'Priority order'),
        ];
    }

    public function getSidebarWidgets(): array
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('homepage');
        $widgetOrders = $module->configuration->widgetOrders;

        $sidebarWidgets = [];
        if ($this->hasWidget(self::WIDGET_SELECTED_CONTENT)) {
            $sidebarWidgets[] = [
                self::WIDGET_SELECTED_CONTENT,
                [],
                ['sortOrder' => $widgetOrders[self::WIDGET_SELECTED_CONTENT] ?? 10],
            ];
        }
        if ($this->hasWidget(self::WIDGET_TOUR)) {
            $sidebarWidgets[] = [
                self::WIDGET_TOUR,
                [],
                ['sortOrder' => $widgetOrders[self::WIDGET_TOUR] ?? 100],
            ];
        }
        if ($this->hasWidget(self::WIDGET_CUSTOM_PAGES)) {
            try {
                $canEdit = PagePermissionHelper::canEdit();
                /* @var CustomPage $page */
                foreach (CustomPagesService::instance()->findByTarget(PageType::TARGET_DASHBOARD_SIDEBAR)->all() as $page) {
                    if ($page->canView()) {
                        $sidebarWidgets[] = [
                            self::WIDGET_CUSTOM_PAGES,
                            ['model' => $page],
                            ['sortOrder' => $page->sort_order ?: 1000 + $page->id],
                        ];
                    }
                }
            } catch (Throwable $e) {
                Yii::error($e, 'homepage');
            }
        }
        if ($this->hasWidget(self::WIDGET_ACTIVITY_STREAM)) {
            $sidebarWidgets[] = [
                self::WIDGET_ACTIVITY_STREAM,
                ['streamAction' => '/dashboard/dashboard/activity-stream'],
                ['sortOrder' => $widgetOrders[self::WIDGET_ACTIVITY_STREAM] ?? 150],
            ];
        }
        if ($this->hasWidget(self::WIDGET_UPCOMING_EVENTS)) {
            $sidebarWidgets[] = [
                self::WIDGET_UPCOMING_EVENTS,
                [],
                ['sortOrder' => $widgetOrders[self::WIDGET_UPCOMING_EVENTS] ?? 300],
            ];
        }
        if ($this->hasWidget(self::WIDGET_NEW_MEMBERS)) {
            $sidebarWidgets[] = [
                self::WIDGET_NEW_MEMBERS,
                [],
                ['sortOrder' => $widgetOrders[self::WIDGET_NEW_MEMBERS] ?? 350],
            ];
        }
        if ($this->hasWidget(self::WIDGET_MOST_ACTIVE_USERS)) {
            $sidebarWidgets[] = [
                self::WIDGET_MOST_ACTIVE_USERS,
                [],
                ['sortOrder' => $widgetOrders[self::WIDGET_MOST_ACTIVE_USERS] ?? 400],
            ];
        }
        if ($this->hasWidget(self::WIDGET_ONLINE_USERS)) {
            $sidebarWidgets[] = [
                self::WIDGET_ONLINE_USERS,
                [],
                ['sortOrder' => $widgetOrders[self::WIDGET_ONLINE_USERS] ?? 450],
            ];
        }
        if ($this->hasWidget(self::WIDGET_NEW_SPACES)) {
            $sidebarWidgets[] = [
                self::WIDGET_NEW_SPACES,
                ['showMoreButton' => true],
                ['sortOrder' => $widgetOrders[self::WIDGET_NEW_SPACES] ?? 500],
            ];
        }
        if ($this->hasWidget(self::WIDGET_MY_TASKS)) {
            $sidebarWidgets[] = [
                self::WIDGET_MY_TASKS,
                [],
                ['sortOrder' => $widgetOrders[self::WIDGET_MY_TASKS] ?? 600],
            ];
        }
        if ($this->hasWidget(self::WIDGET_BIRTHDAY)) {
            $sidebarWidgets[] = [
                self::WIDGET_BIRTHDAY,
                [],
                ['sortOrder' => $widgetOrders[self::WIDGET_BIRTHDAY] ?? 700],
            ];
        }
        return $sidebarWidgets;
    }

    public function hasWidget(string $widget): bool
    {
        $widgets = array_diff($this->getWidgetItems(), $this->getDisabledWidgets());
        return in_array($widget, $widgets, true);
    }

    public function getWidgetItems(): array
    {
        return (array)Json::decode($this->widgets);
    }

    public function getDisabledWidgets(): array
    {
        /** @var \humhub\modules\custom_pages\Module $cpModule */
        $cpModule = Yii::$app->getModule('custom_pages');
        /** @var \humhub\modules\show_content\Module $scModule */
        $scModule = Yii::$app->getModule('show_content');
        /** @var \humhub\modules\calendar\Module $calendarModule */
        $calendarModule = Yii::$app->getModule('calendar');
        /** @var \humhub\modules\newmembers\Module $nmModule */
        $nmModule = Yii::$app->getModule('newmembers');
        /** @var \humhub\modules\mostactiveusers\Module $mauModule */
        $mauModule = Yii::$app->getModule('mostactiveusers');
        /** @var \humhub\modules\onlineUsers\Module $ouModule */
        $ouModule = Yii::$app->getModule('online-users');
        /** @var \humhub\modules\tasks\Module $tasksModule */
        $tasksModule = Yii::$app->getModule('tasks');
        /** @var \humhub\modules\birthday\Module $birthdayModule */
        $birthdayModule = Yii::$app->getModule('birthday');

        $disabledWidgets = [];
        if (!$cpModule?->isEnabled) {
            $disabledWidgets[] = self::WIDGET_CUSTOM_PAGES;
        }
        if (!$scModule?->isEnabled) {
            $disabledWidgets[] = self::WIDGET_SELECTED_CONTENT;
        }
        if (!$calendarModule?->isEnabled) {
            $disabledWidgets[] = self::WIDGET_UPCOMING_EVENTS;
        }
        if (!$nmModule?->isEnabled) {
            $disabledWidgets[] = self::WIDGET_NEW_MEMBERS;
        }
        if (!$mauModule?->isEnabled) {
            $disabledWidgets[] = self::WIDGET_MOST_ACTIVE_USERS;
        }
        if (!$ouModule?->isEnabled) {
            $disabledWidgets[] = self::WIDGET_ONLINE_USERS;
        }
        if (!$tasksModule?->isEnabled) {
            $disabledWidgets[] = self::WIDGET_MY_TASKS;
        }
        if (!$birthdayModule?->isEnabled) {
            $disabledWidgets[] = self::WIDGET_BIRTHDAY;
        }

        if ($this->target === self::TARGET_GUEST) {
            $disabledWidgets[] = self::WIDGET_TOUR;
            $disabledWidgets[] = self::WIDGET_ACTIVITY_STREAM;
            $disabledWidgets[] = self::WIDGET_BIRTHDAY;
        }

        return $disabledWidgets;
    }

    public function isAllowedWidgetsAndLayout(): bool
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('user');
        $settingsManager = $module->settings;

        return
            $this->target !== self::TARGET_GUEST
            || $settingsManager->get('auth.allowGuestAccess');
    }

    /**
     * @inerhitdoc
     */
    public function beforeSave($insert)
    {
        $this->title = $this->title ?: null; // Convert empty string to null
        if (!Yii::$app->user->isAdmin()) {
            $this->content = str_ireplace(
                ['<script>', '</script>'],
                [' - script-allowed-for-sys-admin-only - ', ' - /script-allowed-for-sys-admin-only - '],
                $this->content,
            );
        }
        return parent::beforeSave($insert);
    }
}
