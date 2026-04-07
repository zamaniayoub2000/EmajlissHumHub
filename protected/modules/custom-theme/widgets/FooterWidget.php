<?php

namespace humhub\modules\customTheme\widgets;

use humhub\components\Widget;

class FooterWidget extends Widget
{
    public $content = '';

    public function run()
    {
        if (empty(trim($this->content))) {
            return '';
        }
        return $this->render('footer', ['content' => $this->content]);
    }
}
