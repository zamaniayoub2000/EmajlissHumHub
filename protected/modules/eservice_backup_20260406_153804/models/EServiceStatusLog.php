<?php

namespace humhub\modules\eservice\models;

use Yii;
use yii\db\ActiveRecord;
use humhub\modules\user\models\User;

/**
 * This is the model class for table "e_service_status_log".
 *
 * @property int $id
 * @property int $request_id
 * @property string $old_status
 * @property string $new_status
 * @property string $comment
 * @property int $changed_by
 * @property string $created_at
 *
 * @property EServiceRequest $request
 * @property User $changedByUser
 */
class EServiceStatusLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'e_service_status_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_id', 'new_status', 'changed_by'], 'required'],
            [['request_id', 'changed_by'], 'integer'],
            [['new_status', 'old_status'], 'string', 'max' => 50],
            [['comment'], 'safe'],
            [['request_id'], 'exist', 'targetClass' => EServiceRequest::class, 'targetAttribute' => 'id'],
            [['changed_by'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'request_id' => 'Demande',
            'old_status' => 'Ancien statut',
            'new_status' => 'Nouveau statut',
            'comment' => 'Commentaire',
            'changed_by' => 'Modifie par',
            'created_at' => 'Date de creation',
        ];
    }

    /**
     * Returns the related service request.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequest()
    {
        return $this->hasOne(EServiceRequest::class, ['id' => 'request_id']);
    }

    /**
     * Returns the user who changed the status.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChangedByUser()
    {
        return $this->hasOne(User::class, ['id' => 'changed_by']);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
        }

        return true;
    }
}
