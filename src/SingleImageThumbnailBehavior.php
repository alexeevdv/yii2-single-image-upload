<?php

namespace alexeevdv\image;

use Imagine\Image\Palette\Color;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
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
    public $sourcePath = '@frontend/web/uploads';

    /**
     * @var string
     */
    public $destinationPath;

    /**
     * @var string
     */
    public $baseUrl;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->destinationPath === null) {
            $this->destinationPath = $this->sourcePath;
        }
        parent::init();
    }

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

        if (file_exists($this->generateThumbnailPath($attribute, $type))) {
            return $this->generateUrl($attribute, $type);
        }

        $thumbnail = new SingleImageThumbnail($this->thumbnails[$type]);

        if (empty($this->owner->$attribute) || !file_exists($this->generateSourcePath($attribute))) {
            return $this->generatePlaceholderUrl($thumbnail);
        }

        $image = $this->generateThumbnailImage($this->generateSourcePath($attribute), $thumbnail);
        $image->save($this->generateThumbnailPath($attribute, $type));

        return $this->generateUrl($attribute, $type);
    }

    /**
     * @param ImageInterface $image
     * @param SingleImageThumbnail $thumbnail
     * @return ImageInterface
     */
    protected function ensureImageSize(ImageInterface $image, SingleImageThumbnail $thumbnail)
    {
        if ($image->getSize()->getWidth() >= $thumbnail->getWidth() && $image->getSize()->getWidth() >= $thumbnail->getHeight()) {
            return $image;
        }

        $ratio = max(
            $thumbnail->getWidth() / $image->getSize()->getWidth(),
            $thumbnail->getHeight() / $image->getSize()->getHeight()
        );

        $newSize = new Box(
            ceil($image->getSize()->getWidth() * $ratio),
            ceil($image->getSize()->getHeight() * $ratio)
        );

        return $image->resize($newSize);
    }

    /**
     * @param ImageInterface $image
     * @param SingleImageThumbnail $thumbnail
     * @return ImageInterface
     */
    protected function ensureImagePads(ImageInterface $image, SingleImageThumbnail $thumbnail)
    {
        if ($thumbnail->getMode() !== SingleImageThumbnail::MODE_INSET) {
            return $image;
        }

        $x = 0;
        $y = 0;

        $imageWidth = $image->getSize()->getWidth();
        $imageHeight = $image->getSize()->getHeight();

        if ($thumbnail->getWidth() > $imageWidth) {
            $x =  round(($thumbnail->getWidth() - $imageWidth) / 2);
        } elseif ($thumbnail->getHeight() > $imageHeight) {
            $y = round(($thumbnail->getHeight() - $imageHeight) / 2);
        }

        $palette = new RGB;
        $color = $palette->color($thumbnail->getBackgroundColor(), $thumbnail->getBackgroundOpacity());
        return Image::getImagine()
            ->create(new Box($thumbnail->getWidth(), $thumbnail->getHeight()), $color)
            ->paste($image, new Point($x, $y));
    }

    /**
     * @param string $attribute
     * @param string $type
     * @return string
     */
    protected function generateThumbnailPath($attribute, $type)
    {
        return rtrim(Yii::getAlias($this->destinationPath), '/') . '/' . $type . '-' . $this->owner->$attribute;
    }

    /**
     * @param string $attribute
     * @return string
     */
    protected function generateSourcePath($attribute)
    {
        return rtrim(Yii::getAlias($this->sourcePath), '/') . '/' . $this->owner->$attribute;
    }

    /**
     * @param string $attribute
     * @param string $type
     * @return string
     */
    protected function generateUrl($attribute, $type)
    {
        if ($this->baseUrl) {
            return Url::to(rtrim(Yii::getAlias($this->baseUrl), '/') . '/' . $type . '-' . $this->owner->$attribute);
        }

        $fullPath =  rtrim($this->destinationPath, '/') . '/' . $type . '-' . $this->owner->$attribute;
        $baseUrl = str_replace('@frontend/web', '', $fullPath);
        return Url::to($baseUrl);
    }

    /**
     * @param SingleImageThumbnail $thumbnail
     * @return string
     */
    protected function generatePlaceholderUrl(SingleImageThumbnail $thumbnail)
    {
        return 'http://placehold.it/' . $thumbnail->getWidth() . 'x' . $thumbnail->getHeight();
    }

    /**
     * @param string $sourcePath
     * @param SingleImageThumbnail $thumbnail
     * @return ImageInterface
     */
    protected function generateThumbnailImage($sourcePath, SingleImageThumbnail $thumbnail)
    {
        $image = Image::getImagine()->open($sourcePath);
        $image = $this->ensureImageSize($image, $thumbnail);
        $image = $image->thumbnail(new Box($thumbnail->getWidth(), $thumbnail->getHeight()), $thumbnail->getMode());
        return $this->ensureImagePads($image, $thumbnail);
    }
}
