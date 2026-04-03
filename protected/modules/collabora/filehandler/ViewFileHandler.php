<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\collabora\filehandler;

use humhub\modules\file\handler\BaseFileHandler;
use Yii;
use yii\helpers\Url;

/**
 * ViewFileHandler provides the view of a text file
 *
 * @author Luke
 */
class ViewFileHandler extends BaseFileHandler
{
    /**
     * @inheritdoc
     */
    public $position = self::POSITION_TOP;

    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        return [
            'label' => Yii::t('CollaboraModule.base', 'View'),
            'data-action-url' => Url::to(['/collabora/editor', 'view' => 1, 'guid' => $this->file->guid]),
            'data-action-click' => 'ui.modal.load',
            'data-modal-id' => 'collabora-modal',
            'data-modal-close' => '',
        ];
    }

}
