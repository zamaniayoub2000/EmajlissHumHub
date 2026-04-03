<?php

namespace humhub\modules\eservice\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * EServiceRequestSearch represents the model behind the search form for EServiceRequest.
 *
 * Used for filtering requests in the admin panel.
 *
 * @property string $date_from
 * @property string $date_to
 */
class EServiceRequestSearch extends EServiceRequest
{
    /**
     * @var string Date range filter start
     */
    public $date_from;

    /**
     * @var string Date range filter end
     */
    public $date_to;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['type', 'sub_type', 'status', 'event_name', 'date_from', 'date_to'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // Bypass parent scenarios to allow all attributes for search
        return \yii\base\Model::scenarios();
    }

    /**
     * Creates a data provider instance with search query applied.
     *
     * @param array $params Search parameters
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = EServiceRequest::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filter by exact match fields
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'sub_type' => $this->sub_type,
            'status' => $this->status,
        ]);

        // Filter by event name (partial match)
        $query->andFilterWhere(['like', 'event_name', $this->event_name]);

        // Filter by date range on created_at
        if (!empty($this->date_from)) {
            $query->andFilterWhere(['>=', 'created_at', $this->date_from . ' 00:00:00']);
        }

        if (!empty($this->date_to)) {
            $query->andFilterWhere(['<=', 'created_at', $this->date_to . ' 23:59:59']);
        }

        return $dataProvider;
    }
}
