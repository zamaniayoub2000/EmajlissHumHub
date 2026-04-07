<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\eservice\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use humhub\components\Controller;
use humhub\modules\eservice\models\EServiceRequest;

/**
 * IndexController handles the main E-Services landing page and user dashboard.
 *
 * @package humhub\modules\eservice\controllers
 */
class IndexController extends Controller
{
    /**
     * {@inheritdoc}
     *
     * Requires authentication for all actions.
     *
     * @return array the behavior configuration
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::class,
            ],
        ];
    }

    /**
     * Renders the main E-Services page displaying the 5 service cards.
     *
     * The service cards include: Hebergement, Billet d'avion, Document,
     * Indemnite, and Support.
     *
     * @return string the rendered view
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Displays the current user's requests dashboard.
     *
     * Lists all service requests submitted by the authenticated user,
     * ordered by creation date (most recent first).
     *
     * @return string the rendered view
     */
    public function actionDashboard()
    {
        $user = Yii::$app->user->identity;

        $dataProvider = new ActiveDataProvider([
            'query' => EServiceRequest::find()
                ->where(['user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('dashboard', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
