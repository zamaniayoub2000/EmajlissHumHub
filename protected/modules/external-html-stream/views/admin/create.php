<?php

/**
 * @var \humhub\modules\ui\view\components\View $this
 * @var \humhub\modules\externalHtmlStream\models\ExternalPost $model
 * @var \humhub\modules\space\models\Space[] $spaces
 */

$this->title = Yii::t('ExternalHtmlStreamModule.base', 'Nouvelle publication HTML externe');

echo $this->render('_form', [
    'model'  => $model,
    'spaces' => $spaces,
]);
