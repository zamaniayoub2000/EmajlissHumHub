<?php

namespace humhub\modules\externalHtmlStream\widgets;

use humhub\components\Widget;
use humhub\modules\externalHtmlStream\models\ExternalPost;

/**
 * ExternalHtmlWidget — Widget réutilisable pour afficher du contenu HTML externe.
 *
 * Usage:
 * ```php
 * ExternalHtmlWidget::widget(['postId' => 123]);
 * ExternalHtmlWidget::widget(['model' => $externalPost]);
 * ```
 */
class ExternalHtmlWidget extends Widget
{
    /** @var int|null ID du post à afficher */
    public $postId;

    /** @var ExternalPost|null Modèle à afficher directement */
    public $model;

    /** @var bool Afficher le titre */
    public $showTitle = true;

    /** @var bool Afficher le bouton rafraîchir */
    public $showRefreshButton = true;

    /** @var bool Utiliser le mode iframe (sandbox) */
    public $useIframe = false;

    /** @var string|null Hauteur max du conteneur */
    public $maxHeight = '500px';

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->model === null && $this->postId !== null) {
            $this->model = ExternalPost::findOne($this->postId);
        }

        if ($this->model === null) {
            return '';
        }

        $this->model->refreshIfNeeded();

        return $this->render('externalHtmlWidget', [
            'model'             => $this->model,
            'showTitle'         => $this->showTitle,
            'showRefreshButton' => $this->showRefreshButton,
            'useIframe'         => $this->useIframe,
            'maxHeight'         => $this->maxHeight,
        ]);
    }
}
