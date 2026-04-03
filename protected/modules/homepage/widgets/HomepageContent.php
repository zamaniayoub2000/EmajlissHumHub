<?php

/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

namespace humhub\modules\homepage\widgets;

use humhub\components\Widget;
use humhub\modules\homepage\models\Homepage;

class HomepageContent extends Widget
{
    public Homepage $homepage;

    /**
     * @inerhitdoc
     */
    public function run()
    {
        return $this->render('homepageContent', [
            'homepage' => $this->homepage,
        ]);
    }
}
