<?php

namespace humhub\modules\eservice\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "e_service_event".
 *
 * @property int $id
 * @property string $name
 * @property int $is_active
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 */
class EServiceEvent extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'e_service_event';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['is_active'], 'boolean'],
            [['is_active'], 'default', 'value' => 1],
            [['sort_order'], 'integer'],
            [['sort_order'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Nom',
            'is_active' => 'Actif',
            'sort_order' => 'Ordre',
            'created_at' => 'Date de creation',
            'updated_at' => 'Date de modification',
        ];
    }

    /**
     * Returns all active events ordered by sort_order.
     *
     * @return static[]
     */
    public static function getActiveEvents()
    {
        return static::find()
            ->where(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();
    }
}
