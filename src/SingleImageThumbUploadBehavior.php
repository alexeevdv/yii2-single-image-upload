<?php

namespace alexeevdv\image;

use Imagine\Image\Box;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\imagine\Image;
use yii\web\UploadedFile;

/**
 * Class SingleImageThumbUploadBehavior
 * @package alexeevdv\image
 */
class SingleImageThumbUploadBehavior extends Behavior
{
    /** @var string
     * only 'thumb' or 'resize'
     */
    public $mode = 'thumb';

    /** @var  array */
    public $size;

    /** @var  array|string */
    public $attributes;

    /**
     * @var string
     */
    public $imageUploadPath = '@frontend/web/uploads';

    /**
     * @var string
     */
    public $thumbUploadPath = '@frontend/web/uploads';

    /**
     * @var null|string
     */
    public $baseUrl = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->mode !== 'thumb' && $this->mode !== 'resize') {
            throw new InvalidConfigException('`mode` param must be `thumb` or `resize`');
        }
        if (!$this->size) {
            throw new InvalidConfigException('`size` param is required');
        }
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
            ActiveRecord::EVENT_AFTER_UPDATE => 'onAfterSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'onAfterSave'
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
                if ($this->mode === 'resize') {
                    Image::getImagine()
                        ->open(rtrim(Yii::getAlias($this->imageUploadPath), '/') . '/' . $filename)
                        ->resize(new Box($this->size['width'], $this->size['height']))
                        ->save(
                            rtrim(Yii::getAlias($this->thumbUploadPath), '/') . '/' . "thumb-$filename",
                            ['quality' => $this->size['quality']]
                        );
                } else {
                    Image::getImagine()
                        ->open(rtrim(Yii::getAlias($this->imageUploadPath), '/') . '/' . $filename)
                        ->thumbnail(new Box($this->size['width'], $this->size['height']))
                        ->save(
                            rtrim(Yii::getAlias($this->thumbUploadPath), '/') . '/' . "thumb-$filename",
                            ['quality' => $this->size['quality']]
                        );
                }
                continue;
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

    /**
     * @param string $attribute
     * @return string
     */
    public function getThumb($attribute)
    {
        if ($this->baseUrl) {
            return Url::to(rtrim(Yii::getAlias($this->baseUrl), '/') . '/' . 'thumb-' . $this->owner->$attribute);
        }

        $filePath =  rtrim($this->thumbUploadPath, '/') . '/' . 'thumb-' . $this->owner->$attribute;
        $basePath = str_replace('@frontend/web', '', $filePath);
        return Url::to($basePath);
    }
}
