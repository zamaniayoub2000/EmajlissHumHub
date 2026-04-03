<?php
/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\components\View;
use humhub\helpers\ThemeHelper;

/**
 * @var $this View
 * @var $content string
 */
?>

<div id="homepage" class="container<?= ThemeHelper::isFluid() ? '-fluid' : '' ?>">
    <?= $content ?>
</div>
