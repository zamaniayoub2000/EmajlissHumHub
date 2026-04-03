<?php

/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\homepage\helpers;

use humhub\components\Event;
use humhub\libs\Iso3166Codes;
use humhub\libs\LogoImage;
use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Html;
use yii\web\IdentityInterface;

class StringHelper extends \yii\helpers\StringHelper
{
    public const TAG_PROFILE_FIELD_PREFIX = 'profile_field_';
    public const EVENT_REPLACE_PAIR = 'homepage_replace_pair';
    public const EVENT_REPLACE_TAGS = 'homepage_replace_tags';

    public static function replaceTags(string $text, bool $html = false, User|IdentityInterface|null $user = null): string
    {
        $user = $user ?? Yii::$app->user->identity;

        if ($user) {
            $text = self::replaceProfileFields($text, $user);
        }

        $replacePairs = self::getReplacePairs($user, $html);
        $replacePairs['\<br />'] = '<br>'; // bug
        $text = strtr($text, $replacePairs);

        $evt = new Event(['result' => $text]);
        Event::trigger(static::class, static::EVENT_REPLACE_TAGS, $evt);
        return $evt->result;
    }

    public static function replaceProfileFields(string $text, User|IdentityInterface $user, int|false $offset = 0, array $fieldsToReplace = []): string
    {
        $search = '{' . self::TAG_PROFILE_FIELD_PREFIX;
        $searchLength = strlen($search);
        $offset = ($offset !== false) ? strpos($text, $search, $offset) : false;
        if ($offset !== false) {
            $startPos = $offset + $searchLength;
            $endPos = strpos($text, '}', $offset);
            $fieldName = substr(
                $text,
                $startPos,
                $endPos - $startPos,
            );
            $searchedPattern = $search . $fieldName . '}';
            if (
                !array_key_exists($searchedPattern, $fieldsToReplace)
                && $user->profile->hasAttribute($fieldName)
            ) {
                $fieldsToReplace[$searchedPattern] = $user->profile->$fieldName;
            }
            return self::replaceProfileFields($text, $user, $endPos, $fieldsToReplace);
        }
        return strtr($text, $fieldsToReplace);
    }

    public static function getReplacePairs(User|IdentityInterface|null $user = null, bool $html = false): array
    {
        $user = $user ?? Yii::$app->user->identity;
        $profile = $user?->profile;

        $pairs = [];

        if ($html) {
            $pairs = [
                '{platformLogo}' => LogoImage::hasImage() ? Html::img(LogoImage::getUrl()) : '',
            ];
        }

        $pairs = array_merge($pairs, [
            '{platformName}' => static::encode((string)Yii::$app->settings->get('name'), $html),
            '{dateToday}' => Yii::$app->formatter->asDate(time(), 'short'),
            '{userUsername}' => $user?->username ?? '',
            '{userEmail}' => $user?->email ?? '',
            '{userFirstname}' => static::encode($profile?->firstname ?? '', $html),
            '{userLastname}' => static::encode($profile?->lastname ?? '', $html),
            '{userTitle}' => static::encode($profile?->title ?? '', $html),
            '{userDisplayName}' => static::encode($user?->displayName ?? '', $html),
            '{userStreet}' => static::encode($profile?->street ?? '', $html),
            '{userZip}' => static::encode($profile?->zip ?? '', $html),
            '{userCity}' => static::encode($profile?->city ?? '', $html),
            '{userState}' => static::encode($profile?->state ?? '', $html),
            '{userCountry}' => $profile?->country ? static::encode((string)Iso3166Codes::country($profile->country), $html) : '',
            '{userBirthDate}' => $profile?->birthday ? Yii::$app->formatter->asDate($profile->birthday, 'short') : '',
        ]);

        $evt = new Event(['result' => $pairs]);
        Event::trigger(static::class, static::EVENT_REPLACE_PAIR, $evt);
        return $evt->result;
    }

    public static function encode(string $text, bool $html = false): string
    {
        return $html ? Html::encode($text) : $text;
    }

    /**
     * @return array|string[]
     */
    public static function addTagToArrayElements(array $array, string $tagName): array
    {
        return array_map(static function ($element) use ($tagName) {
            return Html::tag($tagName, $element);
        }, $array);
    }
}
