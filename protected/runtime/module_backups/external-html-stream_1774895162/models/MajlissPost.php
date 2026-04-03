<?php

namespace humhub\modules\externalHtmlStream\models;

use Yii;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\externalHtmlStream\widgets\WallEntry;

/**
 * Modèle MajlissPost — Post WordPress Majliss synchronisé dans HumHub.
 *
 * Hérite de ContentActiveRecord pour s'intégrer nativement dans le stream.
 *
 * @property int         $id
 * @property int         $wp_post_id       ID du post dans WordPress
 * @property string      $title            Titre du post
 * @property string|null $content          Contenu texte nettoyé
 * @property string|null $category         Catégorie WordPress
 * @property string|null $wp_date          Date de publication originale dans WP
 * @property string|null $image_url        URL de l'image miniature
 * @property string|null $image_file_guid  GUID du fichier uploadé dans HumHub
 * @property int|null    $space_id         ID de l'espace HumHub cible
 * @property string|null $synced_at        Date de synchronisation
 * @property int         $sync_status      0=erreur, 1=succès, 2=en cours
 * @property string|null $sync_error       Message d'erreur si échec
 * @property string      $created_at
 * @property string      $updated_at
 */
class MajlissPost extends ContentActiveRecord
{
    // Statuts de synchronisation
    const SYNC_ERROR   = 0;
    const SYNC_SUCCESS = 1;
    const SYNC_PENDING = 2;

    /** @inheritdoc */
    protected $streamChannel = 'default';

    /** @inheritdoc */
    public $wallEntryClass = WallEntry::class;

    /** @inheritdoc */
    public $moduleId = 'external-html-stream';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'majliss_synced_post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wp_post_id', 'title'], 'required'],
            [['wp_post_id', 'space_id', 'sync_status'], 'integer'],
            [['title', 'category'], 'string', 'max' => 255],
            [['content', 'sync_error'], 'string'],
            [['image_url'], 'string', 'max' => 1024],
            [['image_file_guid'], 'string', 'max' => 64],
            [['wp_date', 'synced_at', 'created_at', 'updated_at'], 'safe'],
            [['sync_status'], 'default', 'value' => self::SYNC_PENDING],
            [['wp_post_id'], 'unique', 'message' => 'Ce post WordPress a déjà été synchronisé.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'wp_post_id'      => Yii::t('ExternalHtmlStreamModule.base', 'ID WordPress'),
            'title'           => Yii::t('ExternalHtmlStreamModule.base', 'Titre'),
            'content'         => Yii::t('ExternalHtmlStreamModule.base', 'Contenu'),
            'category'        => Yii::t('ExternalHtmlStreamModule.base', 'Catégorie'),
            'wp_date'         => Yii::t('ExternalHtmlStreamModule.base', 'Date WordPress'),
            'image_url'       => Yii::t('ExternalHtmlStreamModule.base', 'URL Image'),
            'image_file_guid' => Yii::t('ExternalHtmlStreamModule.base', 'GUID Fichier HumHub'),
            'space_id'        => Yii::t('ExternalHtmlStreamModule.base', 'Espace'),
            'synced_at'       => Yii::t('ExternalHtmlStreamModule.base', 'Synchronisé le'),
            'sync_status'     => Yii::t('ExternalHtmlStreamModule.base', 'Statut sync'),
            'sync_error'      => Yii::t('ExternalHtmlStreamModule.base', 'Erreur'),
            'created_at'      => Yii::t('ExternalHtmlStreamModule.base', 'Créé le'),
            'updated_at'      => Yii::t('ExternalHtmlStreamModule.base', 'Modifié le'),
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
        $this->updated_at = date('Y-m-d H:i:s');

        return parent::beforeSave($insert);
    }

    /**
     * Vérifie si un post WP est déjà synchronisé.
     *
     * @param int $wpPostId
     * @return bool
     */
    public static function isAlreadySynced(int $wpPostId): bool
    {
        return static::find()
            ->where(['wp_post_id' => $wpPostId])
            ->exists();
    }

    /**
     * Retourne tous les IDs WordPress déjà synchronisés.
     *
     * @return int[]
     */
    public static function getSyncedWpIds(): array
    {
        return static::find()
            ->select('wp_post_id')
            ->column();
    }

    /**
     * Marque le post comme synchronisé avec succès.
     */
    public function markSuccess(): void
    {
        $this->sync_status = self::SYNC_SUCCESS;
        $this->synced_at   = date('Y-m-d H:i:s');
        $this->sync_error  = null;
        $this->updateAttributes(['sync_status', 'synced_at', 'sync_error']);
    }

    /**
     * Marque le post comme échoué.
     *
     * @param string $error
     */
    public function markError(string $error): void
    {
        $this->sync_status = self::SYNC_ERROR;
        $this->sync_error  = $error;
        $this->updateAttributes(['sync_status', 'sync_error']);
    }

    /**
     * Retourne le label HTML du statut.
     *
     * @return string
     */
    public function getSyncStatusLabel(): string
    {
        return match ($this->sync_status) {
            self::SYNC_SUCCESS => '<span class="label label-success"><i class="fa fa-check"></i> Synchronisé</span>',
            self::SYNC_ERROR   => '<span class="label label-danger"><i class="fa fa-times"></i> Erreur</span>',
            self::SYNC_PENDING => '<span class="label label-warning"><i class="fa fa-clock-o"></i> En attente</span>',
            default            => '<span class="label label-default">Inconnu</span>',
        };
    }

    /**
     * Retourne le contenu formaté pour l'affichage dans le stream.
     *
     * @return string
     */
    public function getFormattedContent(): string
    {
        $html = '';

        // Image en haut
        if (!empty($this->image_url)) {
            $html .= '<div class="majliss-post-image" style="margin-bottom: 12px;">';
            $html .= '<img src="' . htmlspecialchars($this->image_url, ENT_QUOTES, 'UTF-8')
                    . '" alt="' . htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8')
                    . '" style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 8px;" loading="lazy">';
            $html .= '</div>';
        }

        // Catégorie + date
        $meta = '';
        if (!empty($this->category)) {
            $meta .= '<span class="label label-primary" style="margin-right: 6px;">'
                    . htmlspecialchars($this->category, ENT_QUOTES, 'UTF-8') . '</span>';
        }
        if (!empty($this->wp_date)) {
            $meta .= '<small class="text-muted"><i class="fa fa-calendar"></i> '
                    . date('d/m/Y', strtotime($this->wp_date)) . '</small>';
        }
        if (!empty($meta)) {
            $html .= '<div style="margin-bottom: 10px;">' . $meta . '</div>';
        }

        // Contenu texte
        $text = htmlspecialchars($this->content ?? '', ENT_QUOTES, 'UTF-8');
        $text = nl2br($text);
        $html .= '<div class="majliss-post-text">' . $text . '</div>';

        return $html;
    }

    /**
     * @inheritdoc
     */
    public function getContentName()
    {
        return Yii::t('ExternalHtmlStreamModule.base', 'Journal du Conseil');
    }

    /**
     * @inheritdoc
     */
    public function getContentDescription()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getIcon()
    {
        return 'fa-newspaper-o';
    }
}
