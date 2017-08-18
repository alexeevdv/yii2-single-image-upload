<?php

namespace backend\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\web\UploadedFile;
use yii\imagine\Image;
use Imagine\Image\Box;
use yii\helpers\Url;

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
    /**
     * @var string
     */
    public $imageUploadPath = '@frontend/web/uploads';

    /**
     * @var string
     */
    public $thumbUploadPath = '@frontend/web/uploads';

    public $basePath = null;

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
        parent::init();
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
        if ($this->mode === 'resize') {
            Image::getImagine()
                ->open(rtrim(Yii::getAlias($this->imageUploadPath), '/') . '/' . $this->owner->$attribute)
                ->resize(new Box($this->size['width'], $this->size['height']))
                ->save(
                    rtrim(Yii::getAlias($this->thumbUploadPath), '/') . '/' . 'thumb-' . $this->owner->$attribute,
                    ['quality' => $this->size['quality']]
                );
        } else {
            Image::getImagine()
                ->open(rtrim(Yii::getAlias($this->imageUploadPath), '/') . '/' . $this->owner->$attribute)
                ->thumbnail(new Box($this->size['width'], $this->size['height']))
                ->save(
                    rtrim(Yii::getAlias($this->thumbUploadPath), '/') . '/' . 'thumb-' . $this->owner->$attribute,
                    ['quality' => $this->size['quality']]
                );
        }

        if ($this->basePath) {
            return Url::to(rtrim(Yii::getAlias($this->basePath), '/') . '/' . 'thumb-' . $this->owner->$attribute);
        }

        $filePath =  rtrim($this->thumbUploadPath, '/') . '/' . 'thumb-' . $this->owner->$attribute;
        $basePath = str_replace('@frontend/web', '', $filePath);
        return Url::to($basePath);
    }
}
