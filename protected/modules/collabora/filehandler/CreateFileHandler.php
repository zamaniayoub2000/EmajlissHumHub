<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\collabora\filehandler;

use humhub\modules\file\handler\BaseFileHandler;
use humhub\modules\ui\icon\widgets\Icon;
use Yii;
use yii\helpers\Url;

/**
 * CreateFileHandler provides the creating of a text file
 *
 * @author Luke
 */
class CreateFileHandler extends BaseFileHandler
{
    public string $icon = 'file-text-o';

    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        return [
            'label' => Icon::get($this->icon) . Yii::t('CollaboraModule.base', 'Create Collabora file <small>(Text, Spreadsheet, Presentation)</small>'),
            'data-action-url' => Url::to(['/collabora/create']),
            'data-action-click' => 'ui.modal.load',
            'data-modal-id' => 'collabora-modal',
            'data-modal-close' => '',
        ];
    }

}
