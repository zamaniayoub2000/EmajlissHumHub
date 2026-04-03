<?php

namespace humhub\modules\customTheme\widgets;

use humhub\components\Widget;

/**
 * Widget d'affichage du footer personnalisé
 * Enveloppe le contenu HTML dans un conteneur identifiable
 */
class FooterWidget extends Widget
{
    /**
     * @var string Contenu HTML du footer
     */
    public $content = '';

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (empty(trim($this->content))) {
            return '';
        }

        return $this->render('footer', [
            'content' => $this->content,
        ]);
    }
}
