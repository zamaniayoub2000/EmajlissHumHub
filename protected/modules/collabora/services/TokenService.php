<?php

namespace humhub\modules\collabora\services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use humhub\modules\file\models\File;
use humhub\modules\user\models\User;
use Yii;

class TokenService
{
    public function __construct(private readonly File $file)
    {
    }

    public function getUserFromAccessToken($token): ?User
    {
        try {
            $validData = JWT::decode($token, new Key($this->getJWTkey(), 'HS256'));
            if (empty($validData->uid) || empty($validData->fileId) || $validData->fileId !== $this->file->id) {
                throw new \RuntimeException();
            }
            return User::find()->where(['id' => $validData->uid])->active()->one();
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
            return null;
        }
    }

    public function getAccessToken(User $user)
    {
        $issuedAt = time();
        $data = [
            'iat' => $issuedAt,
            'uid' => $user->id,
            'fileId' => $this->file->id,
            'exp' => $issuedAt + (int)60 * 60 * 48,
        ];

        return JWT::encode($data, $this->getJWTkey(), 'HS256');
    }

    private function getJWTkey()
    {
        return sha1(Yii::$app->getModule('admin')->settings->get('installationId') . $this->file->guid);
    }
}
