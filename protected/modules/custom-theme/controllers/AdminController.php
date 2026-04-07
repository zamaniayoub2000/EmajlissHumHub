<?php

namespace humhub\modules\customTheme\controllers;

use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\customTheme\models\CustomThemeForm;
use humhub\modules\customTheme\models\CustomThemeSettings;

class AdminController extends Controller
{
    public function actionIndex()
    {
        $form = new CustomThemeForm();
        $form->loadFromDb();

        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            $this->view->saved();
            return $this->redirect(['index']);
        }

        return $this->render('index', ['model' => $form]);
    }

    public function actionFooter()
    {
        $form = new CustomThemeForm();
        $form->loadFromDb();

        if (Yii::$app->request->isPost && $form->load(Yii::$app->request->post()) && $form->save()) {
            $this->view->saved();
            return $this->redirect(['footer']);
        }

        return $this->render('footer', ['model' => $form]);
    }

    public function actionHeader()
    {
        $form = new CustomThemeForm();
        $form->loadFromDb();

        if (Yii::$app->request->isPost && $form->load(Yii::$app->request->post()) && $form->save()) {
            $this->view->saved();
            return $this->redirect(['header']);
        }

        return $this->render('header', ['model' => $form]);
    }

    public function actionCss()
    {
        $form = new CustomThemeForm();
        $form->loadFromDb();

        if (Yii::$app->request->isPost && $form->load(Yii::$app->request->post()) && $form->save()) {
            $this->view->saved();
            return $this->redirect(['css']);
        }

        return $this->render('css', ['model' => $form]);
    }

    public function actionJs()
    {
        $form = new CustomThemeForm();
        $form->loadFromDb();

        if (Yii::$app->request->isPost && $form->load(Yii::$app->request->post()) && $form->save()) {
            $this->view->saved();
            return $this->redirect(['js']);
        }

        return $this->render('js', ['model' => $form]);
    }

    public function actionToggle()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax) {
            return ['success' => false];
        }

        $key = Yii::$app->request->post('key');
        $active = Yii::$app->request->post('active');

        $allowed = ['footer_active', 'header_active', 'css_active', 'js_active', 'sanitize_html'];
        if (!in_array($key, $allowed)) {
            return ['success' => false];
        }

        return ['success' => CustomThemeSettings::setValue($key, $active ? '1' : '0')];
    }
}
