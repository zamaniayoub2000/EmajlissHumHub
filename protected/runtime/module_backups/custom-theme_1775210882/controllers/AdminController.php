<?php

namespace humhub\modules\customTheme\controllers;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\customTheme\models\CustomThemeForm;
use humhub\modules\customTheme\models\CustomThemeSettings;

/**
 * Contrôleur d'administration du module Custom Theme
 * Accessible uniquement aux administrateurs via Administration > Modules > Custom Theme
 */
class AdminController extends Controller
{
    /**
     * Page principale - Dashboard des paramètres
     */
    public function actionIndex()
    {
        $form = new CustomThemeForm();
        $form->loadFromDb();

        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            $this->view->saved();
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $form,
        ]);
    }

    /**
     * Éditeur de footer
     */
    public function actionFooter()
    {
        $form = new CustomThemeForm();
        $form->loadFromDb();

        if (Yii::$app->request->isPost) {
            $form->load(Yii::$app->request->post());
            if ($form->save()) {
                $this->view->saved();
                return $this->redirect(['footer']);
            }
        }

        return $this->render('footer', [
            'model' => $form,
        ]);
    }

    /**
     * Éditeur de header
     */
    public function actionHeader()
    {
        $form = new CustomThemeForm();
        $form->loadFromDb();

        if (Yii::$app->request->isPost) {
            $form->load(Yii::$app->request->post());
            if ($form->save()) {
                $this->view->saved();
                return $this->redirect(['header']);
            }
        }

        return $this->render('header', [
            'model' => $form,
        ]);
    }

    /**
     * Éditeur CSS
     */
    public function actionCss()
    {
        $form = new CustomThemeForm();
        $form->loadFromDb();

        if (Yii::$app->request->isPost) {
            $form->load(Yii::$app->request->post());
            if ($form->save()) {
                $this->view->saved();
                return $this->redirect(['css']);
            }
        }

        return $this->render('css', [
            'model' => $form,
        ]);
    }

    /**
     * Éditeur JS
     */
    public function actionJs()
    {
        $form = new CustomThemeForm();
        $form->loadFromDb();

        if (Yii::$app->request->isPost) {
            $form->load(Yii::$app->request->post());
            if ($form->save()) {
                $this->view->saved();
                return $this->redirect(['js']);
            }
        }

        return $this->render('js', [
            'model' => $form,
        ]);
    }

    /**
     * Toggle rapide d'activation via AJAX
     */
    public function actionToggle()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $key = Yii::$app->request->post('key');
        $active = Yii::$app->request->post('active');

        $allowedKeys = ['footer_active', 'header_active', 'css_active', 'js_active', 'sanitize_html'];
        if (!in_array($key, $allowedKeys)) {
            return ['success' => false, 'message' => 'Invalid key'];
        }

        $result = CustomThemeSettings::setValue($key, $active ? '1' : '0');

        return [
            'success' => $result,
            'message' => $result
                ? Yii::t('CustomThemeModule.base', 'Paramètre mis à jour')
                : Yii::t('CustomThemeModule.base', 'Erreur de mise à jour'),
        ];
    }

    /**
     * Preview AJAX du contenu
     */
    public function actionPreview()
    {
        $type = Yii::$app->request->post('type', 'footer');
        $content = Yii::$app->request->post('content', '');

        return $this->renderAjax('_preview', [
            'type' => $type,
            'content' => $content,
        ]);
    }
}
