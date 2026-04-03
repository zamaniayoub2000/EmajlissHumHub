<?php

namespace humhub\modules\externalHtmlStream\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modèle SyncLog — Journal des opérations de synchronisation.
 *
 * @property int         $id
 * @property string      $level       info, warn, error
 * @property string      $message
 * @property string|null $context     Données contextuelles (JSON)
 * @property string      $created_at
 */
class SyncLog extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'majliss_sync_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message'], 'required'],
            [['level'], 'string', 'max' => 10],
            [['level'], 'default', 'value' => 'info'],
            [['message', 'context'], 'string'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
        }
        return parent::beforeSave($insert);
    }

    /**
     * Enregistre un message de log.
     *
     * @param string $level   info|warn|error
     * @param string $message
     * @param array  $context Données supplémentaires
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        $log = new static();
        $log->level   = $level;
        $log->message = $message;
        $log->context = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : null;
        $log->save(false);

        // Logger aussi dans Yii
        match ($level) {
            'error' => Yii::error($message, 'external-html-stream'),
            'warn'  => Yii::warning($message, 'external-html-stream'),
            default => Yii::info($message, 'external-html-stream'),
        };
    }

    /**
     * Raccourcis.
     */
    public static function info(string $msg, array $ctx = []): void
    {
        static::log('info', $msg, $ctx);
    }

    public static function warn(string $msg, array $ctx = []): void
    {
        static::log('warn', $msg, $ctx);
    }

    public static function error(string $msg, array $ctx = []): void
    {
        static::log('error', $msg, $ctx);
    }

    /**
     * Nettoie les anciens logs (plus de X jours).
     *
     * @param int $days
     * @return int Nombre de lignes supprimées
     */
    public static function cleanup(int $days = 30): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return static::deleteAll(['<', 'created_at', $cutoff]);
    }

    /**
     * Retourne la classe CSS selon le niveau.
     *
     * @return string
     */
    public function getLevelClass(): string
    {
        return match ($this->level) {
            'error' => 'danger',
            'warn'  => 'warning',
            default => 'info',
        };
    }
}
