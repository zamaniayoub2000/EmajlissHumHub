<?php

namespace humhub\modules\eservice\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * This is the model class for table "e_service_file".
 *
 * @property int $id
 * @property int $request_id
 * @property string $filename
 * @property string $original_name
 * @property string $mime_type
 * @property int $file_size
 * @property string $file_path
 * @property string $created_at
 *
 * @property EServiceRequest $request
 */
class EServiceFile extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'e_service_file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_id', 'filename', 'original_name', 'mime_type', 'file_size', 'file_path'], 'required'],
            [['request_id', 'file_size'], 'integer'],
            [['filename', 'original_name'], 'string', 'max' => 255],
            [['mime_type'], 'string', 'max' => 100],
            [['file_path'], 'string', 'max' => 500],
            [['request_id'], 'exist', 'targetClass' => EServiceRequest::class, 'targetAttribute' => 'id'],
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
            'filename' => 'Nom du fichier',
            'original_name' => 'Nom original',
            'mime_type' => 'Type MIME',
            'file_size' => 'Taille du fichier',
            'file_path' => 'Chemin du fichier',
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
     * Returns the download URL for this file.
     *
     * @return string
     */
    public function getDownloadUrl()
    {
        return Url::to(['/eservice/file/download', 'id' => $this->id]);
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
