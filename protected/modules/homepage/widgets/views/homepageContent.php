<?php

use humhub\components\View;
use humhub\modules\homepage\helpers\StringHelper;
use humhub\modules\homepage\models\Homepage;
use humhub\widgets\PanelMenu;
use yii\helpers\Html;

/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

/**
 * @var $this View
 * @var $homepage Homepage
 */
?>

<?php if ($homepage->content_type === Homepage::CONTENT_TYPE_HTML): ?>
    <style>
        #homepage .homepage-content-html a:not(.dropdown a):not(.btn) {
            color: var(--bs-link-color)
        }

        #homepage .homepage-content-html h1.panel-body {
            font-size: <?= $this->theme->variable('font-size-h1') ?: '30px' ?>;
            font-weight: 500;
        }

        #homepage .homepage-content-html h2.panel-body {
            font-size: <?= $this->theme->variable('font-size-h2') ?: '26px' ?>;
        }

        #homepage .homepage-content-html h3.panel-body {
            font-size: <?= $this->theme->variable('font-size-h3') ?: '22px' ?>;
        }

        #homepage .homepage-content-html h4.panel-body {
            font-size: <?= $this->theme->variable('font-size-h4') ?: '18px' ?>;
        }

        #homepage .homepage-content-html > .panel > :first-child {
            margin-top: 0;
        }

        #homepage-content-html-editor, #homepage-content-html-editor > .panel-body {
            border: none;
            box-shadow: none;
            margin: 0;
            padding: 0;
        }
    </style>
<?php endif; ?>

<div id="homepage-content" class="homepage-content-<?= $homepage->content_type ?>">
    <?php if (!$homepage->no_frame) : ?>
    <div class="panel panel-default" id="homepage-content-panel">
        <?php endif; ?>
        <?php if ($homepage->title) : ?>
            <?= PanelMenu::widget(['id' => 'homepage-content-panel']) ?>
            <?php if (!$homepage->no_frame) : ?>
                <div class="panel-heading">
            <?php endif; ?>
            <h3 id="homepage-content-title" style="text-align: center">
                <strong><?= Html::encode(StringHelper::replaceTags($homepage->title)) ?></strong>
            </h3>
            <?php if (!$homepage->no_frame) : ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$homepage->no_frame) : ?>
        <div class="panel-body">
            <?php endif; ?>
            <?php if ($homepage->content_type === Homepage::CONTENT_TYPE_HTML) : ?>
                <div class="row">
                    <div class="col-lg-12 layout-content-container">
                        <?= $homepage->getContentView() ?>
                    </div>
                </div>
            <?php else: ?>
                <?= $homepage->getContentView() ?>
            <?php endif; ?>
            <?php if (!$homepage->no_frame) : ?>
        </div>
    </div>
<?php endif; ?>
</div>
