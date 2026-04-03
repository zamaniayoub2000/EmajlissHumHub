<?php

namespace humhub\modules\homepage\models\forms;

use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\homepage\helpers\StringHelper;
use humhub\modules\homepage\models\Configuration;
use humhub\modules\homepage\models\Homepage;
use humhub\modules\homepage\Module;
use humhub\modules\homepage\permissions\ManageHomepage;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\User;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\modal\ModalButton;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;

class HomepageForm extends Homepage
{
    public string $contentRichText = '';
    public string $contentHtml = '';
    public string $contentIframe = '';
    public string $contentUrl = '';
    public string|array $widgetItems = [];
    private Configuration $_moduleConfiguration;

    public static function getContentTypeOptions(bool $checkIsEnabled = true): array
    {
        $options = [
            self::CONTENT_TYPE_RICH_TEXT => Yii::t('HomepageModule.admin', 'Rich text'),
            self::CONTENT_TYPE_HTML => Yii::t('HomepageModule.admin', 'HTML'),
            self::CONTENT_TYPE_IFRAME => Yii::t('HomepageModule.admin', 'Iframe'),
            self::CONTENT_TYPE_URL => Yii::t('HomepageModule.admin', 'URL (link)'),
        ];

        if ($checkIsEnabled) {
            /** @var Module $module */
            $module = Yii::$app->getModule('homepage');
            $configuration = $module->configuration;
            foreach ($options as $key => $val) {
                if (!$configuration->isContentTypeEnabled($key)) {
                    unset($options[$key]);
                }
            }
        }

        return $options;
    }

    public static function getLayoutOptions(): array
    {
        return [
            self::LAYOUT_CL_DL_SR => Yii::t('HomepageModule.admin', 'On the left. Dashboard below and sidebar on the right.'),
            self::LAYOUT_SL_CR_DR => Yii::t('HomepageModule.admin', 'On the right. Dashboard below and sidebar on the left.'),
            self::LAYOUT_CT_DL_SR => Yii::t('HomepageModule.admin', 'Full width. Below, dashboard (left) and sidebar (right).'),
            self::LAYOUT_CT_SL_DR => Yii::t('HomepageModule.admin', 'Full width. Below, sidebar (left) and dashboard (right).'),
        ];
    }

    public static function getPreviewBtn(string $target, ?int $groupId = null): string
    {
        $homepage = static::getQuery($target, $groupId)->one();
        if (!$homepage) {
            return '';
        }

        $text = Yii::t('HomepageModule.admin', 'Preview');
        $icon = 'eye';

        if ($target === self::CONTENT_TYPE_URL) {
            return Button::info($text)->icon($icon)
                ->link($homepage->content, ['target' => '_blank']);
        }
        return ModalButton::info($text)->icon($icon)
            ->load(['/homepage/index/preview', 'target' => $target, 'groupId' => $groupId]);
    }

    // Get layout options

    /**
     * @inheridoc
     */
    public function load($data, $formName = null): bool
    {
        if (parent::load($data, $formName)) {
            switch ($this->content_type) {
                case self::CONTENT_TYPE_RICH_TEXT:
                    $this->content = $this->contentRichText;
                    break;
                case self::CONTENT_TYPE_HTML:
                    $this->content = $this->contentHtml;
                    break;
                case self::CONTENT_TYPE_IFRAME:
                    $this->content = $this->contentIframe;
                    break;
                case self::CONTENT_TYPE_URL:
                    $this->content = $this->contentUrl;
                    break;
            }
            $this->widgets = Json::encode($this->widgetItems);
            return true;
        }
        return false;
    }

    public function getWidgetOptions(): array
    {
        $options = [
            self::WIDGET_DASHBOARD_STREAM => Yii::t('HomepageModule.admin', 'Dashboard stream'),
            self::WIDGET_TOUR => Yii::t('HomepageModule.admin', 'Getting started'),
            self::WIDGET_CUSTOM_PAGES => Yii::t('HomepageModule.admin', 'Custom pages snippets'),
            self::WIDGET_SELECTED_CONTENT => Yii::t('HomepageModule.admin', 'Selected content'),
            self::WIDGET_ACTIVITY_STREAM => Yii::t('HomepageModule.admin', 'Latest activities'),
            self::WIDGET_UPCOMING_EVENTS => Yii::t('HomepageModule.admin', 'Upcoming events'),
            self::WIDGET_NEW_MEMBERS => Yii::t('HomepageModule.admin', 'New members'),
            self::WIDGET_MOST_ACTIVE_USERS => Yii::t('HomepageModule.admin', 'Most Active Users'),
            self::WIDGET_ONLINE_USERS => Yii::t('HomepageModule.admin', 'Online Users'),
            self::WIDGET_NEW_SPACES => Yii::t('HomepageModule.admin', 'New spaces'),
            self::WIDGET_MY_TASKS => Yii::t('HomepageModule.admin', 'My tasks'),
            self::WIDGET_BIRTHDAY => Yii::t('HomepageModule.admin', 'Birthday'),
            self::WIDGET_FOOTER => Yii::t('HomepageModule.admin', 'Footer'),
        ];

        foreach ($this->getDisabledWidgets() as $disabledWidget) {
            unset($options[$disabledWidget]);
        }

        return $options;
    }

    public function init()
    {
        parent::init();

        /** @var Module $module */
        $module = Yii::$app->getModule('homepage');
        $this->_moduleConfiguration = $module->configuration;

        $this->content_type = $this->_moduleConfiguration->defaultContentType;
        $this->layout = self::LAYOUT_DEFAULT;
        $this->contentHtml = '<div id="homepage-content-html-editor" class="panel panel-default"><div class="panel-body"></div></div>';
    }

    /**
     * @inheridoc
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['contentRichText', 'contentHtml', 'contentIframe', 'contentUrl'], 'string'],
            [['widgetItems'], 'safe'],
        ]);
    }

    /**
     * @inheridoc
     */
    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'contentRichText' => Yii::t('HomepageModule.admin', 'Homepage Content'),
            'contentHtml' => Yii::t('HomepageModule.admin', 'HTML code for the homepage'),
            'contentIframe' => Yii::t('HomepageModule.admin', 'Iframe URL'),
            'contentUrl' => Yii::t('HomepageModule.admin', 'Homepage URL (Link)'),
            'widgetItems' => Yii::t('HomepageModule.admin', 'Widgets'),
        ]);
    }

    /**
     * @inheridoc
     */
    public function attributeHints(): array
    {
        return array_merge(parent::attributeHints(), [
            'title' => $this->getAvailableTagHints('title'),
            'contentRichText' => $this->getAvailableTagHints('contentRichText'),
            'contentHtml'
                => Yii::t('HomepageModule.admin', 'If you add a {scriptTag} tag, the nonce attribute will be automatically added.', [
                    'scriptTag' => Html::tag('code', Html::encode('<script>')),
                ]) . '<br>'
                . $this->getAvailableTagHints('contentHtml', true)
            ,
            'widgetItems' => Yii::t('HomepageModule.admin', 'The order can be changed in the module configuration.'),
            'no_frame' => Yii::t('HomepageModule.admin', 'Don\'t display the content in a frame.'),
        ]);
    }

    public function afterFind()
    {
        parent::afterFind();

        switch ($this->content_type) {
            case self::CONTENT_TYPE_RICH_TEXT:
                $this->contentRichText = $this->content;
                break;
            case self::CONTENT_TYPE_HTML:
                $this->contentHtml = $this->content;
                break;
            case self::CONTENT_TYPE_IFRAME:
                $this->contentIframe = $this->content;
                break;
            case self::CONTENT_TYPE_URL:
                $this->contentUrl = $this->content;
                break;
        }

        // Must be after copying the $this->content because if the type is not enabled it shouldn't be copied to another content type
        if (!$this->_moduleConfiguration->isContentTypeEnabled($this->content_type)) {
            $this->content_type = $this->_moduleConfiguration->defaultContentType;
        }

        $this->widgetItems = $this->getWidgetItems();
    }

    public function getTargetName(): string
    {
        return match ($this->target) {
            self::TARGET_GUEST => Yii::t('HomepageModule.admin', 'Homepage for guests'),
            self::TARGET_REGISTERED => Yii::t('HomepageModule.admin', 'Homepage for registered users'),
            self::TARGET_GROUP => Yii::t('HomepageModule.admin', 'Homepage for {groupName} group', [
                '{groupName}' => '"' . Html::encode($this->group?->name ?? 'GROUP NOT FOUND!') . '"',
            ]),
            default => Yii::t('HomepageModule.admin', 'Create a new homepage'),
        };
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        switch ($this->content_type) {
            case self::CONTENT_TYPE_RICH_TEXT:
                RichText::postProcess($this->content, $this);
                break;
            case self::CONTENT_TYPE_HTML:
                $this->fileManager->attach(Yii::$app->request->post('fileList'));
                break;
        }
    }

    /**
     * Triggered when attached image is delete
     * @throws InvalidConfigException
     */
    public function canEdit($user = null): bool
    {
        $user = $user ? User::findOne($user) : Yii::$app->user->identity; // TODO: use \humhub\modules\user\helpers\UserHelper::getUserByParam($user) instead, when HumHub minimal version is 1.18.0 stable
        return (new PermissionManager(['subject' => $user]))->can(ManageHomepage::class);
    }

    private function getAvailableTagHints(string $property, bool $html = false)
    {
        $id = 'toggle-content-' . $property;
        return Html::a(
            Yii::t('HomepageModule.admin', 'Available tags') . ' ' . Icon::get('angle-down'),
            '#' . $id,
            ['data-bs-toggle' => 'collapse'],
        )
            . Html::tag(
                'div',
                implode(', ', StringHelper::addTagToArrayElements(array_keys(StringHelper::getReplacePairs(null, $html)), 'code')) . ' '
                . Yii::t('HomepageModule.admin', 'or any profile field using {profile_field_replace_with_internal_name}', [
                    'profile_field_replace_with_internal_name' => Html::tag('code', '{profile_field_replace_with_internal_name}'),
                ]),
                ['id' => $id, 'class' => 'collapse'],
            );
    }
}
