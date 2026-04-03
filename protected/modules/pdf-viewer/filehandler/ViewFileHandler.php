<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\pdfViewer\filehandler;

use humhub\modules\file\handler\BaseFileHandler;
use humhub\modules\pdfViewer\helpers\Url;
use Yii;

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
            'label' => Yii::t('PdfViewerModule.base', 'View PDF'),
            'data-action-url' => Url::toView($this->file),
            'data-action-click' => 'ui.modal.load',
            'data-modal-id' => 'pdf-viewer-modal',
            'data-modal-close' => '',
        ];
    }
}
