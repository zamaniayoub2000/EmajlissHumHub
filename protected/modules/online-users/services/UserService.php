<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\onlineUsers\services;

use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\components\Session;
use humhub\modules\user\models\User;
use humhub\modules\user\services\IsOnlineService;
use Yii;
use yii\caching\DummyCache;

class UserService
{
    /**
     * Max number of users to check each one from cache by IsOnlineService
     * @var int $maxUsersCheckFromCache
     */
    public int $maxUsersCheckFromCache = 50;

    /**
     * @var User[] $users
     */
    private ?array $users = null;
    private ?int $count = null;

    public function __construct(?int $maxUsersCheckFromCache = null)
    {
        if ($maxUsersCheckFromCache !== null) {
            $this->maxUsersCheckFromCache = $maxUsersCheckFromCache;
        }
    }

    public function query(): ActiveQueryUser
    {
        return Session::getOnlineUsers()->visible();
    }

    public function getCount(): int
    {
        if ($this->count === null) {
            $this->count = count($this->getUsers());
        }

        return $this->count;
    }

    /**
     * @param int|null $limit
     * @return User[]
     */
    public function getUsers(?int $limit = null): array
    {
        if ($this->users === null) {
            $this->users = $this->query()->all();

            // Filter users with online service (from cache/green dot)
            if (!(Yii::$app->cache instanceof DummyCache) && // Don't check when cache is disabled
                count($this->users) < $this->maxUsersCheckFromCache) {
                $this->users = array_filter($this->users, function (User $user) {
                    return (new IsOnlineService($user))->getStatus();
                });
            }
        }

        if ($limit > 0) {
            $this->users = array_slice($this->users, 0, $limit);
        }

        return $this->users;
    }
}
