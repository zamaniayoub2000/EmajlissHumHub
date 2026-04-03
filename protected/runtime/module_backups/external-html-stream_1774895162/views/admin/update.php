<?php

/**
 * @var \humhub\modules\ui\view\components\View $this
 * @var \humhub\modules\externalHtmlStream\models\ExternalPost $model
 * @var \humhub\modules\space\models\Space[] $spaces
 */

$this->title = Yii::t('ExternalHtmlStreamModule.base', 'Modifier la publication');

echo $this->render('_form', [
    'model'  => $model,
    'spaces' => $spaces,
]);
