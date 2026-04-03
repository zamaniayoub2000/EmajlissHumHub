<?php

namespace humhub\modules\externalHtmlStream\models;

use Yii;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\externalHtmlStream\widgets\WallEntry;

/**
 * Modèle ExternalPost — Publication HTML externe (API générique).
 *
 * Hérite de ContentActiveRecord pour s'intégrer dans le stream HumHub.
 *
 * @property int         $id
 * @property string      $title
 * @property string      $api_url          URL de l'API externe
 * @property int         $refresh_interval Intervalle de rafraîchissement en secondes
 * @property string|null $last_fetched_at  Dernière récupération
 * @property string|null $cached_html      Contenu HTML mis en cache
 * @property int|null    $space_id
 * @property string      $created_at
 * @property string      $updated_at
 */
class ExternalPost extends ContentActiveRecord
{
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
        return 'external_html_post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'api_url'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['api_url'], 'url', 'defaultScheme' => 'https'],
            [['refresh_interval'], 'integer', 'min' => 60, 'max' => 86400],
            [['refresh_interval'], 'default', 'value' => 3600],
            [['cached_html'], 'string'],
            [['space_id'], 'integer'],
            [['last_fetched_at', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'title'            => Yii::t('ExternalHtmlStreamModule.base', 'Titre'),
            'api_url'          => Yii::t('ExternalHtmlStreamModule.base', 'URL de l\'API'),
            'refresh_interval' => Yii::t('ExternalHtmlStreamModule.base', 'Intervalle de rafraîchissement (secondes)'),
            'last_fetched_at'  => Yii::t('ExternalHtmlStreamModule.base', 'Dernière récupération'),
            'cached_html'      => Yii::t('ExternalHtmlStreamModule.base', 'Contenu HTML'),
            'space_id'         => Yii::t('ExternalHtmlStreamModule.base', 'Espace'),
            'created_at'       => Yii::t('ExternalHtmlStreamModule.base', 'Créé le'),
            'updated_at'       => Yii::t('ExternalHtmlStreamModule.base', 'Modifié le'),
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
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Récupérer le contenu HTML à la création
        if ($insert && empty($this->cached_html)) {
            $this->fetchContent();
        }
    }

    /**
     * Récupère le contenu HTML depuis l'API externe via cURL.
     *
     * @return bool true si la récupération a réussi
     */
    public function fetchContent(): bool
    {
        $module = Yii::$app->getModule('external-html-stream');
        $timeout = (int) $module->getModuleSetting('apiTimeout', 30);

        try {
            $ch = curl_init($this->api_url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_TIMEOUT        => $timeout,
                CURLOPT_USERAGENT      => 'HumHub-ExternalHtmlStream/1.0',
                CURLOPT_HTTPHEADER     => [
                    'Accept: text/html, application/json',
                ],
            ]);

            $body     = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($curlErr || $httpCode !== 200 || empty($body)) {
                Yii::error(
                    "API fetch failed for post #{$this->id}: HTTP $httpCode, cURL: $curlErr",
                    'external-html-stream'
                );
                return false;
            }

            // Déterminer si JSON ou HTML
            if (str_contains($contentType ?? '', 'application/json')) {
                $data = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $html = $data['html'] ?? $data['content'] ?? $data['body'] ?? '';
                } else {
                    $html = $body;
                }
            } else {
                $html = $body;
            }

            // Nettoyer et stocker le HTML
            $this->cached_html = $this->sanitizeHtml($html);
            $this->last_fetched_at = date('Y-m-d H:i:s');

            $this->updateAttributes([
                'cached_html'     => $this->cached_html,
                'last_fetched_at' => $this->last_fetched_at,
            ]);

            Yii::info("Content fetched for post #{$this->id}", 'external-html-stream');
            return true;

        } catch (\Exception $e) {
            Yii::error(
                "Unexpected error fetching post #{$this->id}: " . $e->getMessage(),
                'external-html-stream'
            );
            return false;
        }
    }

    /**
     * Vérifie si le contenu doit être rafraîchi.
     *
     * @return bool
     */
    public function needsRefresh(): bool
    {
        if (empty($this->last_fetched_at)) {
            return true;
        }
        return (time() - strtotime($this->last_fetched_at)) >= $this->refresh_interval;
    }

    /**
     * Rafraîchit le contenu si nécessaire.
     *
     * @param bool $force
     * @return bool
     */
    public function refreshIfNeeded(bool $force = false): bool
    {
        if ($force || $this->needsRefresh()) {
            return $this->fetchContent();
        }
        return true;
    }

    /**
     * Nettoie le HTML via HTMLPurifier.
     *
     * @param string $html
     * @return string
     */
    protected function sanitizeHtml(string $html): string
    {
        $module = Yii::$app->getModule('external-html-stream');

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', Yii::$app->runtimePath . '/htmlpurifier');

        $allowedTags = $module->allowedHtmlTags;
        if ($module->getModuleSetting('allowIframes', false)) {
            $allowedTags[] = 'iframe';
        }

        $config->set('HTML.AllowedElements', $allowedTags);
        $config->set('HTML.AllowedAttributes', [
            '*.class', '*.id', '*.style',
            'a.href', 'a.target', 'a.rel',
            'img.src', 'img.alt', 'img.width', 'img.height',
            'iframe.src', 'iframe.width', 'iframe.height', 'iframe.frameborder', 'iframe.allowfullscreen',
        ]);
        $config->set('CSS.AllowedProperties', [
            'color', 'background-color', 'background', 'font-size', 'font-family',
            'font-weight', 'text-align', 'text-decoration', 'margin', 'padding',
            'border', 'border-radius', 'width', 'height', 'max-width', 'max-height',
            'display', 'flex', 'justify-content', 'align-items', 'gap',
            'box-shadow', 'opacity', 'line-height', 'overflow',
        ]);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'data' => true]);

        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }

    /**
     * Retourne le contenu HTML à afficher, avec fallback.
     *
     * @return string
     */
    public function getDisplayHtml(): string
    {
        $module = Yii::$app->getModule('external-html-stream');
        $cacheEnabled = $module->getModuleSetting('enableCache', true);

        if ($cacheEnabled && !empty($this->cached_html) && !$this->needsRefresh()) {
            return $this->cached_html;
        }

        if ($this->fetchContent()) {
            return $this->cached_html;
        }

        if (!empty($this->cached_html)) {
            return $this->cached_html;
        }

        return '<div class="alert alert-warning">'
            . Yii::t('ExternalHtmlStreamModule.base', 'Contenu indisponible. L\'API ne répond pas.')
            . '</div>';
    }

    /**
     * @inheritdoc
     */
    public function getContentName()
    {
        return Yii::t('ExternalHtmlStreamModule.base', 'Contenu externe');
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
        return 'fa-globe';
    }

    /**
     * Retourne les posts qui nécessitent un rafraîchissement.
     *
     * @return static[]
     */
    public static function findNeedingRefresh(): array
    {
        $posts = static::find()->all();
        $result = [];

        foreach ($posts as $post) {
            if ($post->needsRefresh()) {
                $result[] = $post;
            }
        }

        return $result;
    }
}
