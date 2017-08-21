<?php

namespace alexeevdv\image;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidParamException;
use yii\helpers\Url;
use yii\imagine\Image;

/**
 * Class SingleImageThumbnailBehavior
 * @package alexeevdv\image
 */
class SingleImageThumbnailBehavior extends Behavior
{
    /**
     * @var array
     */
    public $thumbnails = [];

    /**
     * @var string
     */
    public $uploadPath = '@frontend/web/uploads';

    /**
     * @var string
     */
    public $basePath = null;

    /**
     * @param string $attribute
     * @param string $type
     * @return string
     */
    public function getThumbnail($attribute, $type)
    {
        if (!isset($this->thumbnails[$type])) {
            throw new InvalidParamException('Invalid thumbnail type: ' . $type);
        }
        $thumbnail = $this->thumbnails[$type];

        if (file_exists($this->generateThumbnailPath($attribute, $type))) {
            return $this->generateUrl($attribute, $type);
        }

        $image = Image::getImagine()->open($this->generateSourcePath($attribute));
        if (!$this->checkImageSize($image, $thumbnail['width'], $thumbnail['height'])) {
            $image = $this->enlargeImage($image, $thumbnail['width'], $thumbnail['height']);
        }

        $mode = isset($thumbnail['mode']) ? $thumbnail['mode'] : ImageInterface::THUMBNAIL_OUTBOUND;
        $image = $image->thumbnail(new Box($thumbnail['width'], $thumbnail['height']), $mode);
        $image->save($this->generateThumbnailPath($attribute, $type));

        return $this->generateUrl($attribute, $type);
    }

    /**
     * @param ImageInterface $image
     * @param int $width
     * @param int $height
     * @return bool
     */
    protected function checkImageSize(ImageInterface $image, $width, $height)
    {
        return $image->getSize()->getWidth() >= $width && $image->getSize()->getWidth() >= $height;
    }

    /**
     * @param ImageInterface $image
     * @param int $width
     * @param int $height
     * @return ImageInterface
     */
    protected function enlargeImage(ImageInterface $image, $width, $height)
    {
        // TODO: implement image resize
        return $image;
    }

    /**
     * @param string $attribute
     * @param string $type
     * @return string
     */
    protected function generateThumbnailPath($attribute, $type)
    {
        return rtrim(Yii::getAlias($this->uploadPath), '/') . '/' . $type . '-' . $this->owner->$attribute;
    }

    /**
     * @param string $attribute
     * @return string
     */
    protected function generateSourcePath($attribute)
    {
        return rtrim(Yii::getAlias($this->uploadPath), '/') . '/' . $this->owner->$attribute;
    }

    /**
     * @param string $attribute
     * @param string $type
     * @return string
     */
    protected function generateUrl($attribute, $type)
    {
        if ($this->basePath) {
            return Url::to(rtrim(Yii::getAlias($this->basePath), '/') . '/' . $type . '-' . $this->owner->$attribute);
        }

        $fullPath =  rtrim($this->uploadPath, '/') . '/' . $type . '-' . $this->owner->$attribute;
        $basePath = str_replace('@frontend/web', '', $fullPath);
        return Url::to($basePath);
    }
}
