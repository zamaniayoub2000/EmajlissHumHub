<?php

/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\homepage\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\homepage\models\forms\HomepageForm;
use humhub\modules\homepage\models\Homepage;
use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class IndexController extends Controller
{
    protected const LAYOUT_GUEST_NOT_ALLOWED = 'layout-ct';
    protected const LAYOUT_VIEW_NAMES = [
        Homepage::LAYOUT_CL_DL_SR => 'layout-cl-dl-sr',
        Homepage::LAYOUT_SL_CR_DR => 'layout-sl-cr-dr',
        Homepage::LAYOUT_CT_DL_SR => 'layout-ct-dl-sr',
        Homepage::LAYOUT_CT_SL_DR => 'layout-ct-sl-dr',
    ];

    public function beforeAction($action)
    {
        // Allow homepage for guests even if "Allow visitors limited access" is disabled
        if (Yii::$app->user->isGuest && Homepage::getForGuest()) {
            $this->access = ControllerAccess::class;
        }

        return parent::beforeAction($action);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $this->subLayout = '@homepage/views/index/_sub_layout';

        $homepage = Yii::$app->user->isGuest
            ? Homepage::getForGuest()
            : Homepage::getForUser();

        if (!$homepage) {
            return $this->redirect(
                Url::current() === Url::home()
                ? ['/dashboard/dashboard']
                : ['/'],
            );
        }

        if ($homepage->content_type === Homepage::CONTENT_TYPE_URL) {
            return Yii::$app->controller->redirect((string)$homepage->content);
        }

        return $this->render(
            $this->getLayoutViewName($homepage),
            [
                'homepage' => $homepage,
            ],
        );
    }

    protected function getLayoutViewName(Homepage $homepage): string
    {
        return $homepage->isAllowedWidgetsAndLayout()
            ? self::LAYOUT_VIEW_NAMES[$homepage->layout] ?? self::LAYOUT_VIEW_NAMES[Homepage::LAYOUT_DEFAULT]
            : self::LAYOUT_GUEST_NOT_ALLOWED;
    }

    public function actionPreview($target, $groupId = null)
    {
        $homepage = HomepageForm::getQuery($target, $groupId)->one();
        if (!$homepage) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $this->renderAjax('@homepage/views/index/_preview_sub_layout', [
            'title' => $homepage->getTargetName(),
            'content' => $this->renderPartial($this->getLayoutViewName($homepage), [
                'homepage' => $homepage,
            ]),
        ]);
    }
}
