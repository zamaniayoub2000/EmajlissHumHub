<?php
/**
 * Homepage
 * @link https://www.cuzy.app
 * @license https://www.cuzy.app/cuzy-license
 * @author [Marc FARRE](https://marc.fun)
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\content\widgets\richtext\RichTextField;
use humhub\modules\custom_pages\widgets\TinyMce;
use humhub\modules\file\widgets\FilePreview;
use humhub\modules\file\widgets\UploadButton;
use humhub\modules\file\widgets\UploadProgress;
use humhub\modules\homepage\models\forms\HomepageForm;
use humhub\modules\homepage\models\Homepage;
use humhub\modules\ui\form\widgets\CodeMirrorInputWidget;
use humhub\widgets\form\SortOrderField;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/**
 * @var $this View
 * @var $model HomepageForm
 */

$useTinyMce = class_exists(TinyMce::class);
?>

<?php $form = Modal::beginFormDialog([
    'title' => $model->getTargetName(),
    'size' => Modal::SIZE_LARGE,
    'footer' => ModalButton::cancel() . ' ' . ModalButton::save()->submit(),
    'form' => ['id' => 'home-page-form'],
]) ?>

    <?= $form->field($model, 'enabled')->checkbox() ?>

    <?php if ($model->target === Homepage::TARGET_GROUP) : ?>
        <?= $form->field($model, 'group_priority_order')->widget(SortOrderField::class) ?>
    <?php endif; ?>

    <?php
    $contentTypeOptions = HomepageForm::getContentTypeOptions();
    $contentTypeField = $form->field($model, 'content_type', ['options' => [
        'id' => 'hp-field-content-type',
    ]]);
    ?>

    <?php if (count($contentTypeOptions) > 1) : ?>
        <?= $contentTypeField->dropDownList($contentTypeOptions) ?>
    <?php else: ?>
        <?= $contentTypeField->hiddenInput()->label(false) ?>
    <?php endif; ?>

    <?= $form->field($model, 'title', ['options' => [
        'id' => 'hp-field-title',
    ]])->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'contentRichText', ['options' => [
        'class' => 'hp-field-content',
        'data-content-type' => Homepage::CONTENT_TYPE_RICH_TEXT,
    ]])->widget(RichTextField::class, ['pluginOptions' => ['maxHeight' => '500px']]) ?>

    <div class="hp-field-content" data-content-type="<?= Homepage::CONTENT_TYPE_HTML ?>">
        <?php if ($useTinyMce) : ?>
            <?= $form->field($model, 'contentHtml')->widget(TinyMce::class, [
            'options' => ['id' => 'contentHtml'],
            'clientOptions' => [
                'humhubTrigger' => [
                    'icon' => 'upload',
                    'text' => Yii::t('HomepageModule.admin', 'Attach Files'),
                    'selector' => '#homepage-html-file-upload',
                    'event' => 'click',
                ],
                'valid_children' => '+body[style]',  // Allows style tags in body
                'extended_valid_elements' => 'style[type|media|scoped]',  // Allows style tag with common attributes
            ],
        ]) ?>
        <?php else: ?>
            <?= $form->field($model, 'contentHtml')->widget(CodeMirrorInputWidget::class) ?>
        <?php endif; ?>
        <?= UploadButton::widget([
            'id' => 'homepage-html-file-upload',
            'label' => Yii::t('HomepageModule.admin', 'Attach Files'),
            'tooltip' => false,
            'hideInStream' => true,
            'progress' => '#homepage-html-upload-progress',
            'preview' => '#homepage-html-upload-preview',
            'cssButtonClass' => 'btn-light btn-sm',
            'model' => $model,
        ]) ?>
        <?= FilePreview::widget([
            'id' => 'homepage-html-upload-preview',
            'options' => ['style' => 'margin-top:10px'],
            'model' => $model,
            'edit' => true,
        ]) ?>
        <?= UploadProgress::widget(['id' => 'homepage-html-upload-progress']) ?>
        <br><br>
    </div>

    <?= $form->field($model, 'contentIframe', ['options' => [
        'class' => 'hp-field-content',
        'data-content-type' => Homepage::CONTENT_TYPE_IFRAME,
    ]])->textInput(['type' => 'url']) ?>

    <?= $form->field($model, 'contentUrl', ['options' => [
        'class' => 'hp-field-content',
        'data-content-type' => Homepage::CONTENT_TYPE_URL,
    ]])->textInput(['type' => 'url']) ?>

    <?= $form->field($model, 'no_frame')->checkbox() ?>

    <?php if ($model->isAllowedWidgetsAndLayout()) : ?>
        <?= $form->field($model, 'widgetItems', ['options' => [
            'id' => 'hp-field-widget-items',
        ]])->checkboxList($model->getWidgetOptions()) ?>

        <?= $form->field($model, 'layout', ['options' => [
            'id' => 'hp-field-layout',
        ]])->dropDownList(HomepageForm::getLayoutOptions()) ?>
    <?php endif; ?>

<?php Modal::endFormDialog()?>

<script <?= Html::nonce() ?>>
    $(function () {
        setTimeout(function () {
            <?php if ($useTinyMce) : ?>
            // Before submit
            $('#home-page-form button[type="submit"]').on('click', function (e) {
                // Copy the HTML content in the TinyMCE iframe back to the original textarea
                tinyMCE.triggerSave();
            });

            <?php else: ?>
            // Hack to make CodeMirror work in a modal box
            const hpCmTextArea = document.getElementById('homepageform-contenthtml');
            let hpCmEditor = CodeMirror.fromTextArea(hpCmTextArea, {
                lineNumbers: true
            });
            hpCmEditor.on("change", function (cm, change) {
                cm.save();
            });

            // Insert file in HTML content after upload
            const humhubFileUploadWidget = $('#homepage-html-file-upload').data('humhubFileUpload');
            humhubFileUploadWidget.on('humhub:file:uploadEnd', function (evt, response) {
                if (!(response._response.result.files instanceof Array) ||
                    !response._response.result.files.length) {
                    return;
                }
                hpCmEditor.setValue(hpCmEditor.getValue() + getFileHtmlTags(response._response.result.files));
            });

            function getFileHtmlTags(files) {
                let htmlTags = '\n';
                files.forEach(function (file) {
                    if (typeof (file.url) === 'undefined' || typeof (file.mimeType) === 'undefined') {
                        return;
                    }
                    if (file.mimeType.indexOf('image/') === 0) {
                        htmlTags += '<img src="' + file.url + '" class="img-fluid" alt="' + file.name + '">';
                    } else {
                        htmlTags += '<a href="' + file.url + '" target="_blank">' + (typeof (file.name) === 'undefined' ? file.url : file.name) + '</a>';
                    }
                    htmlTags += '\n';
                });
                return htmlTags;
            }
            <?php endif; ?>

            // Toggle content field from the selected type (must be done after the "Hack to make CodeMirror work in a modal box")
            const $contentTypeField = $('#hp-field-content-type input, #hp-field-content-type select');
            const toggleContentField = function () {
                const contentType = $contentTypeField.val();
                $('.hp-field-content').hide();
                $('.hp-field-content[data-content-type="' + contentType + '"]').show();
                if (contentType === '<?= Homepage::CONTENT_TYPE_URL ?>') {
                    $('#hp-field-title, #hp-field-widget-items, #hp-field-layout').hide();
                } else {
                    $('#hp-field-title, #hp-field-widget-items, #hp-field-layout').show();
                }
            };
            toggleContentField();
            $contentTypeField.on('change', toggleContentField);
        }, 250);
    });
</script>
<style>
    .CodeMirror {
        border: 1px solid #CCC;
    }

    /* TinyMCE "Upgrade" button */
    .tox-tinymce .tox-promotion {
        display: none;
    }
</style>
