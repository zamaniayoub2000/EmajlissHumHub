<?php

namespace humhub\modules\eservice\models;

use Yii;
use yii\db\ActiveRecord;
use humhub\modules\user\models\User;

/**
 * This is the model class for table "e_service_request".
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $sub_type
 * @property string $status
 * @property string $event_name
 * @property string $date_start
 * @property string $date_end
 * @property string $observations
 * @property string $flight_plan
 * @property string $admin_comment
 * @property int $shuttle_arrival
 * @property int $shuttle_departure
 * @property string $extra_data
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 * @property EServiceFile[] $files
 * @property EServiceStatusLog[] $statusLogs
 */
class EServiceRequest extends ActiveRecord
{
    /** Type constants */
    const TYPE_HEBERGEMENT = 'hebergement';
    const TYPE_BILLET_AVION = 'billet_avion';
    const TYPE_DOCUMENT = 'document';
    const TYPE_INDEMNITE = 'indemnite';
    const TYPE_SUPPORT = 'support';

    /** Status constants */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /** Document sub-type constants */
    const SUBTYPE_RESERVATION = 'reservation';
    const SUBTYPE_BULLETIN = 'bulletin';
    const SUBTYPE_DOSSIER = 'dossier';
    const SUBTYPE_DOCUMENTATION = 'documentation';
    const SUBTYPE_PROPOSITION = 'proposition';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'e_service_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'user_id', 'status'], 'required'],
            [['user_id'], 'integer'],
            [['type'], 'string', 'max' => 50],
            [['type'], 'in', 'range' => array_keys(static::getTypesList())],
            [['sub_type'], 'string', 'max' => 50],
            [['sub_type'], 'in', 'range' => array_keys(static::getSubTypesList())],
            [['status'], 'in', 'range' => array_keys(static::getStatusesList())],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['date_start', 'date_end'], 'date', 'format' => 'php:Y-m-d'],
            [['event_name'], 'string', 'max' => 255],
            [['observations', 'flight_plan', 'admin_comment'], 'string'],
            [['shuttle_arrival', 'shuttle_departure'], 'boolean'],
            [['extra_data'], 'safe'],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Utilisateur',
            'type' => 'Type de demande',
            'sub_type' => 'Sous-type',
            'status' => 'Statut',
            'event_name' => 'Manifestation',
            'date_start' => 'Date de debut',
            'date_end' => 'Date de fin',
            'observations' => 'Observations',
            'flight_plan' => 'Plan de vol',
            'admin_comment' => 'Commentaire administrateur',
            'shuttle_arrival' => 'Navette arrivee',
            'shuttle_departure' => 'Navette depart',
            'extra_data' => 'Donnees supplementaires',
            'created_at' => 'Date de creation',
            'updated_at' => 'Date de modification',
        ];
    }

    /**
     * Returns the related user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Returns the related files.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(EServiceFile::class, ['request_id' => 'id']);
    }

    /**
     * Returns the related status logs ordered by most recent first.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatusLogs()
    {
        return $this->hasMany(EServiceStatusLog::class, ['request_id' => 'id'])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Returns the French label for the current type.
     *
     * @return string
     */
    public function getTypeLabel()
    {
        $types = static::getTypesList();
        return isset($types[$this->type]) ? $types[$this->type] : $this->type;
    }

    /**
     * Returns the French label for the current status.
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $statuses = static::getStatusesList();
        return isset($statuses[$this->status]) ? $statuses[$this->status] : $this->status;
    }

    /**
     * Returns the CSS badge class for the current status.
     *
     * @return string
     */
    public function getStatusBadgeClass()
    {
        $classes = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
        ];

        return isset($classes[$this->status]) ? $classes[$this->status] : 'default';
    }

    /**
     * Returns the French label for the current document sub_type.
     *
     * @return string
     */
    public function getSubTypeLabel()
    {
        $subTypes = static::getSubTypesList();
        return isset($subTypes[$this->sub_type]) ? $subTypes[$this->sub_type] : $this->sub_type;
    }

    /**
     * Returns the associative array of type => French label.
     *
     * @return array
     */
    public static function getTypesList()
    {
        return [
            self::TYPE_HEBERGEMENT => 'Hebergement',
            self::TYPE_BILLET_AVION => 'Billet d\'avion',
            self::TYPE_DOCUMENT => 'Document',
            self::TYPE_INDEMNITE => 'Dépôt de documents',
            self::TYPE_SUPPORT => 'Support',
        ];
    }

    /**
     * Returns the associative array of status => French label.
     *
     * @return array
     */
    public static function getStatusesList()
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_APPROVED => 'Approuvee',
            self::STATUS_REJECTED => 'Rejetee',
        ];
    }

    /**
     * Returns the associative array of sub_type => French label.
     *
     * @return array
     */
    public static function getSubTypesList()
    {
        return [
            self::SUBTYPE_RESERVATION => 'Reservation',
            self::SUBTYPE_BULLETIN => 'Bulletin',
            self::SUBTYPE_DOSSIER => 'Dossier',
            self::SUBTYPE_DOCUMENTATION => 'Documentation',
            self::SUBTYPE_PROPOSITION => 'Proposition',
        ];
    }

    /**
     * Returns the list of active events from the e_service_event table.
     *
     * @return array
     */
    public static function getEventsList()
    {
        return EServiceEvent::find()
            ->where(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        if ($insert) {
            $this->created_at = $now;
        }

        $this->updated_at = $now;

        return true;
    }
}
