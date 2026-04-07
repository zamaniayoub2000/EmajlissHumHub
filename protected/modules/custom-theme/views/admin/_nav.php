<?php
use Yii;
/** @var string $active */
?>
<ul class="nav nav-pills nav-stacked custom-theme-nav">
    <li class="<?= $active === 'index' ? 'active' : '' ?>">
        <a href="<?= \yii\helpers\Url::to(['/custom-theme/admin/index']) ?>">
            <i class="fa fa-dashboard"></i> Dashboard
        </a>
    </li>
    <li class="<?= $active === 'footer' ? 'active' : '' ?>">
        <a href="<?= \yii\helpers\Url::to(['/custom-theme/admin/footer']) ?>">
            <i class="fa fa-arrow-down"></i> Footer
        </a>
    </li>
    <li class="<?= $active === 'header' ? 'active' : '' ?>">
        <a href="<?= \yii\helpers\Url::to(['/custom-theme/admin/header']) ?>">
            <i class="fa fa-arrow-up"></i> Header
        </a>
    </li>
    <li class="<?= $active === 'css' ? 'active' : '' ?>">
        <a href="<?= \yii\helpers\Url::to(['/custom-theme/admin/css']) ?>">
            <i class="fa fa-css3"></i> CSS
        </a>
    </li>
    <li class="<?= $active === 'js' ? 'active' : '' ?>">
        <a href="<?= \yii\helpers\Url::to(['/custom-theme/admin/js']) ?>">
            <i class="fa fa-code"></i> JavaScript
        </a>
    </li>
</ul>
