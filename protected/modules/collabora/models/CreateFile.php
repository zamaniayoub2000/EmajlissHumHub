<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\collabora\models;

use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use Yii;
use yii\base\Model;

/**
 * CreateFile is a form for create a new text file
 *
 * @author Luke
 */
class CreateFile extends Model
{
    /**
     * @var string
     */
    public $fileName;

    public $fileType = 'docx';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fileName', 'fileType'], 'required'],
            ['fileType', 'in', 'range' => array_keys($this->getFileTypes())],
            ['fileName', 'string', 'max' => 255],
        ];
    }


    public function getFileTypes()
    {
        return [
            'docx' => 'Text Document (.docx)',
            'xlsx' => 'Spreadsheet (.xlsx)',
            'pptx' => 'Presentation (.pptx)',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fileName' => Yii::t('CollaboraModule.base', 'Filename'),
            'fileType' => Yii::t('CollaboraModule.base', 'Type'),
        ];
    }

    public function attributeHints()
    {
        return [
            'fileType' => 'Choose the desired document type. The correct extension will be appended automatically.',
        ];
    }


    /**
     * @return false|File
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $file = new File();
        $file->file_name = $this->fileName . '.' . $this->fileType;
        $file->size = 0;
        $file->mime_type = FileHelper::getMimeTypeByExtension($file->file_name);
        if (!$file->save()) {
            return false;
        }

        $file->store->setContent('');

        return $file;
    }

}
