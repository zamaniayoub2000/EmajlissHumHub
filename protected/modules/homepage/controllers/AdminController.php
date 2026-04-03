<?php

/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\homepage\controllers;

use humhub\modules\admin\components\Controller;
use humhub\modules\admin\models\GroupSearch;
use humhub\modules\homepage\models\forms\HomepageForm;
use humhub\modules\homepage\models\Homepage;
use humhub\modules\homepage\permissions\ManageHomepage;
use Yii;

/**
 *
 * @property-read array[] $accessRules
 */
class AdminController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new GroupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $enabledGroupHomepageIds = Homepage::find()
            ->where(['enabled' => true])
            ->select('group_id')
            ->column();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'enabledGroupHomepageIds' => $enabledGroupHomepageIds,
        ]);
    }

    public function actionEdit($target, $groupId = null)
    {
        $model = HomepageForm::getQuery($target, $groupId)->one()
            ?? new HomepageForm(['target' => $target, 'group_id' => $groupId]);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
            $this->view->saved();
            return $this->redirect(['index']);
        }

        return $this->renderAjax('edit', [
            'model' => $model,
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permissions' => ManageHomepage::class],
        ];
    }
}
