<?php

namespace humhub\modules\externalHtmlStream\widgets;

use humhub\modules\content\widgets\stream\WallStreamEntryWidget;
use humhub\modules\externalHtmlStream\models\MajlissPost;
use humhub\modules\externalHtmlStream\assets\ExternalHtmlStreamAsset;

/**
 * WallEntry — Widget d'affichage dans le stream HumHub.
 *
 * Gère l'affichage des deux types de contenu :
 *  - MajlissPost (posts WordPress synchronisés) → carte article
 *  - ExternalPost (contenu API externe) → affichage HTML
 */
class WallEntry extends WallStreamEntryWidget
{
    /** @inheritdoc */
    public $editRoute = '/external-html-stream/admin/update';

    /**
     * @inheritdoc
     */
    public function renderContent()
    {
        // Enregistrer les assets CSS + JS
        ExternalHtmlStreamAsset::register($this->getView());

        // Déterminer le template selon le type de modèle
        if ($this->model instanceof MajlissPost) {
            return $this->render('wallEntryMajliss', [
                'model' => $this->model,
            ]);
        }

        return $this->render('wallEntry', [
            'model' => $this->model,
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function getTitle()
    {
        return $this->model->title ?? '';
    }

    /**
     * @inheritdoc
     */
    protected function getIcon()
    {
        if ($this->model instanceof MajlissPost) {
            return 'newspaper-o';
        }
        return 'globe';
    }
}
