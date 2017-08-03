<?php

namespace alexeevdv\image;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\validators\ImageValidator;
use yii\web\UploadedFile;

/**
 * Class SingleImageUploadBehavior
 * @package alexeevdv\image
 */
class SingleImageUploadBehavior extends Behavior
{
    /**
     * @var string|array
     */
    public $attributes;

    /**
     * @var array
     */
    public $validatorOptions = [
        'extensions' => 'jpg, png',
    ];

    /**
     * @var string
     */
    public $uploadPath = '@frontend/web/uploads';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->attributes) {
            throw new InvalidConfigException('`attributes` param is required');
        }
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_VALIDATE => 'onAfterValidate',
            ActiveRecord::EVENT_AFTER_UPDATE => 'onAfterSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'onAfterSave',
        ];
    }

    /**
     * EVENT_AFTER_UPDATE and EVENT_AFTER_INSERT event handler
     */
    public function onAfterSave()
    {
        $attributes = is_array($this->attributes) ? $this->attributes : (array) $this->attributes;
        foreach ($attributes as $attribute) {
            $file = UploadedFile::getInstance($this->owner, $attribute);
            if ($file) {
                $filename = $this->generateFilename($file);
                $file->saveAs(rtrim(Yii::getAlias($this->uploadPath), '/') . '/' . $filename);
                $this->owner->updateAttributes([
                    $attribute => $filename,
                ]);
                continue;
            }
            if (!$this->owner->{$attribute}) {
                $this->owner->updateAttributes([
                    $attribute => null,
                ]);
            }
        }
    }

    /**
     * EVENT_AFTER_VALIDATE event handler
     */
    public function onAfterValidate()
    {
        $attributes = is_array($this->attributes) ? $this->attributes : (array) $this->attributes;
        foreach ($attributes as $attribute) {
            $file = UploadedFile::getInstance($this->owner, $attribute);
            if (!$file) {
                continue;
            }
            $validator = Yii::createObject(ImageValidator::className(), [$this->validatorOptions]);
            if (!$validator->validate($file, $error)) {
                $this->owner->addError($attribute, $error);
            }
        }
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    private function generateFilename(UploadedFile $file)
    {
        return $file->baseName . '.' . $file->extension;
    }
}
