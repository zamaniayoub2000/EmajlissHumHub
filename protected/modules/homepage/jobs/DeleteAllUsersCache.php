<?php

/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\homepage\jobs;

use humhub\modules\homepage\models\Homepage;
use humhub\modules\queue\ActiveJob;
use humhub\modules\user\models\User;
use Yii;

class DeleteAllUsersCache extends ActiveJob
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        foreach (User::find()->column() as $userId) {
            Yii::$app->cache->delete(Homepage::getCacheId($userId));
        }
    }
}
