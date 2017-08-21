<?php

namespace backend\behaviors;

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
 * Class ImageResizeBehavior
 * @package backend\behaviors
 */
class ImageResizeBehavior extends Behavior
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
        $size = $this->thumbnails[$type];
        if (!$size) {
            throw new InvalidParamException('`type` param is missing in model behavior config');
        }

        if (file_exists(rtrim(Yii::getAlias($this->thumbUploadPath), '/') . '/' . $type . '-' . $this->owner->$attribute)) {
            if ($this->basePath) {
                return Url::to(rtrim(Yii::getAlias($this->basePath), '/') . '/' . $type . '-' . $this->owner->$attribute);
            }

            $fullPath =  rtrim($this->thumbUploadPath, '/') . '/' . $type . '-' . $this->owner->$attribute;
            $basePath = str_replace('@frontend/web', '', $fullPath);
            return Url::to($basePath);
        }

        $image = Image::getImagine()
            ->open(rtrim(Yii::getAlias($this->imageUploadPath), '/') . '/' . $this->owner->$attribute);

        $width = $image->getSize()->getWidth();
        $height = $image->getSize()->getHeight();
        $ratio = $width / $height;

        if ($width < $size['width'] || ($height < $size['height'])) {
            if ($size['width'] > $size['height']) {
                $image->resize(new Box($size['width'], $size['width'] / $ratio));
            } else {
                $image->resize(new Box($size['height'] * $ratio, $size['height']));
            }
            $width = $image->getSize()->getWidth();
            $height = $image->getSize()->getHeight();
        }

        $box = new Box($width, $height);
        $center = new Center($box);
        $centerX = $center->getX() - $size['width'] / 2;
        $centerY = $center->getY() - $size['height'] / 2;

        $image->crop(new Point($centerX, $centerY), new Box($size['width'], $size['height']))
            ->save(
                rtrim(Yii::getAlias($this->thumbUploadPath), '/') . '/' . $type . '-' . $this->owner->$attribute
            );

        if ($this->basePath) {
            return Url::to(rtrim(Yii::getAlias($this->basePath), '/') . '/' . $type . '-' . $this->owner->$attribute);
        }

        $filePath = rtrim($this->thumbUploadPath, '/') . '/' . $type . '-' . $this->owner->$attribute;
        $basePath = str_replace('@frontend/web', '', $filePath);
        return Url::to($basePath);
    }
}
