<?php
/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\components\View;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\helpers\Html;

/**
 * @var $this View
 * @var $content string
 * @var $title string
 */
?>

<?= Modal::widget([
    'id' => 'homepage-preview',
    'title' => Html::tag('strong', $title),
    'body' => $content,
    'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
    'size' => Modal::SIZE_LARGE,
    'dialogOptions' => [
        'style' => 'width: calc(100% - 40px); max-width: 1400px',
    ],
]) ?>
