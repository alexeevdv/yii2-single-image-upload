<?php

namespace alexeevdv\image;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\web\UploadedFile;
use yii\imagine\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\Point\Center;
use yii\helpers\Url;

/**
 * Class SingleImageThumbUploadBehavior
 * @package alexeevdv\image
 */
class SingleImageThumbUploadBehavior extends Behavior
{
    /** @var  array */
    public $thumbnails;

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
    public $basePath = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->thumbnails) {
            throw new InvalidConfigException('`thumbnails` param is required');
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
    public function getThumbnail($attribute, $type)
    {
        if (file_exists(rtrim(Yii::getAlias($this->thumbUploadPath), '/') . '/' . 'thumb-' . $this->owner->$attribute)) {
            if ($this->basePath) {
                return Url::to(rtrim(Yii::getAlias($this->basePath), '/') . '/' . 'thumb-' . $this->owner->$attribute);
            }

            $filePath =  rtrim($this->thumbUploadPath, '/') . '/' . 'thumb-' . $this->owner->$attribute;
            $basePath = str_replace('@frontend/web', '', $filePath);
            return Url::to($basePath);
        }

        $size = $this->thumbnails[$type];
        if (!$size) {
            throw new InvalidParamException('`type` param is missing in model behavior config');
        }

        $image = Image::getImagine()
            ->open(rtrim(Yii::getAlias($this->imageUploadPath), '/') . '/' . $this->owner->$attribute);

        $width = $image->getSize()->getWidth();
        $height = $image->getSize()->getHeight();
        $ratio = $width / $height;

        if ($width < $size['width'] || ($height < $size['height'])) {
            if ($size['width'] > $size['height']) {
                $image->resize(new Box($size['height'] * $ratio, $size['height']));
            } else {
                $image->resize(new Box($size['width'], $size['width'] / $ratio));
            }
        }

//        $newWidth = $image->getSize()->getWidth();
//        $newHeight = $image->getSize()->getHeight();
//        $box = new Box($newWidth, $newHeight);
//        $center = new Center($box);

        $image->crop(new Point(0, 0), new Box($size['width'], $size['height']))
            ->save(
                rtrim(Yii::getAlias($this->thumbUploadPath), '/') . '/' . 'thumb-' . $this->owner->$attribute
            );

        if ($this->basePath) {
            return Url::to(rtrim(Yii::getAlias($this->basePath), '/') . '/' . 'thumb-' . $this->owner->$attribute);
        }

        $filePath = rtrim($this->thumbUploadPath, '/') . '/' . 'thumb-' . $this->owner->$attribute;
        $basePath = str_replace('@frontend/web', '', $filePath);
        return Url::to($basePath);
    }
}
