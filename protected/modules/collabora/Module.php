<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\collabora;

use humhub\modules\collabora\models\Configuration;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use yii\helpers\Url;

class Module extends \humhub\components\Module
{
    private ?Configuration $configuration = null;

    /**
     * @inheritdoc
     */
    public function getConfigUrl()
    {
        return Url::to(['/collabora/config']);
    }

    /**
     * Check the file type is supported by this module
     *
     * @param File|null $file
     * @return bool
     */
    public function isSupportedType(?File $file): bool
    {
        $allowedFormats = ['ODT', 'ODS', 'ODP', 'ODG', 'DOC', 'DOCX', 'XLS', 'XLSX', 'PPT', 'PPTX', 'TXT', 'PDF'];

        if (in_array(strtoupper(FileHelper::getExtension($file)), $allowedFormats)) {
            return true;
        }

        return false;
    }

    public function canView(File $file): bool
    {
        return $this->isSupportedType($file)
            && $file->canRead()
            && is_readable($file->getStore()->get());
    }

    public function getConfiguration(): Configuration
    {
        if ($this->configuration === null) {
            $this->configuration = new Configuration(['settingsManager' => $this->settings]);
            $this->configuration->loadBySettings();
        }
        return $this->configuration;
    }
}
