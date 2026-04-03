<?php

namespace humhub\modules\customTheme\widgets;

use humhub\components\Widget;

/**
 * Widget d'affichage du header personnalisé
 * Injecte le contenu HTML en haut du body
 */
class HeaderWidget extends Widget
{
    /**
     * @var string Contenu HTML du header
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

        return $this->render('header', [
            'content' => $this->content,
        ]);
    }
}
