<?php

namespace humhub\modules\collabora\controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use humhub\components\Controller;
use humhub\modules\collabora\services\TokenService;
use humhub\modules\file\models\File;
use humhub\modules\user\models\User;
use Yii;
use yii\web\HttpException;

class WopiController extends Controller
{
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     * Allow server to server configuration without any authentication
     */
    public $access = \humhub\components\access\ControllerAccess::class;

    private ?File $file = null;
    private ?User $user = null;


    public function beforeAction($action)
    {
        $this->file = File::find()->where(['id' => (int)Yii::$app->request->get('fileId')])->one();
        $this->user = (new TokenService($this->file))->getUserFromAccessToken($_REQUEST['access_token']);

        if ($this->user === null) {
            Yii::error("Access Token invalid" . print_r($_REQUEST['access_token'], true));
            throw new HttpException(401, "Invalid Access Token " . $_REQUEST['access_token']);
        }

        return parent::beforeAction($action);
    }


    public function actionHead($fileId = 0)
    {
        $response = [
            'BaseFileName' => $this->file->file_name,
            'Size' => $this->file->size,
            'UserId' => $this->user->id,
            'UserCanWrite' => $this->file->canDelete($this->user),
            'UserFriendlyName' => $this->user->displayName,
        ];
        return $this->asJson($response);
    }

    public function actionGet()
    {
        Yii::$app->response->sendFile($this->file->store->get(), $this->file->file_name);
    }

    public function actionPost()
    {
        if (!$this->file->canDelete($this->user->id)) {
            throw new HttpException(400);
        }

        $this->file->setStoredFileContent(Yii::$app->request->getRawBody());
        $this->file->updateAttributes([
            'updated_at' => date("Y-m-d H:i:s"),
            'size' => filesize($this->file->store->get()),
            'updated_by' => $this->user->id,
        ]);

        Yii::$app->end();
    }


}
