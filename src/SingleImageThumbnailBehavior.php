<?php

namespace alexeevdv\image;

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
    public $baseUrl = null;

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

        $thumbnail = $this->thumbnails[$type];
        $width = $thumbnail['width'];
        $height = $thumbnail['height'];
        $mode = isset($thumbnail['mode']) ? $thumbnail['mode'] : ImageInterface::THUMBNAIL_OUTBOUND;
        $bg_color = isset($thumbnail['bg-color']) ? $thumbnail['bg-color'] : '#fff';
        $opacity = isset($thumbnail['opacity']) ? $thumbnail['opacity'] : 100;

        if (file_exists($this->generateThumbnailPath($attribute, $type))) {
            return $this->generateUrl($attribute, $type);
        }

        $image = Image::getImagine()->open($this->generateSourcePath($attribute));

        if (!$this->ensureImageSize($image, $width, $height)) {
            $image = $this->enlargeImage($image, $width, $height);
        }

        $image = $image->thumbnail(new Box($width, $height), $mode);

        if ($this->ensureImagePads($mode)) {
            $image = $this->padImage($image, $width, $height, $bg_color, $opacity);
        }

        $image->save($this->generateThumbnailPath($attribute, $type));

        return $this->generateUrl($attribute, $type);
    }

    /**
     * @param ImageInterface $image
     * @param int $width
     * @param int $height
     * @return bool
     */
    protected function ensureImageSize(ImageInterface $image, $width, $height)
    {
        return $image->getSize()->getWidth() >= $width && $image->getSize()->getWidth() >= $height;
    }

    /**
     * @param string $mode
     * @return bool
     */
    protected function ensureImagePads($mode)
    {
        return $mode === ImageInterface::THUMBNAIL_INSET;
    }

    /**
     * @param ImageInterface $image
     * @param int $width
     * @param int $height
     * @return ImageInterface
     */
    protected function enlargeImage(ImageInterface $image, $width, $height)
    {
        // Calculate ratio of desired maximum sizes and original sizes.
        $widthRatio = $width / $image->getSize()->getWidth();
        $heightRatio = $height / $image->getSize()->getHeight();

        // Ratio used for calculating new image dimensions.
        $ratio = max($widthRatio, $heightRatio);

        // Calculate new image dimensions.
        $newWidth  = (int)$image->getSize()->getWidth()  * $ratio;
        $newHeight = (int)$image->getSize()->getHeight() * $ratio;

        return $image->resize(new Box($newWidth,$newHeight));
    }

    /**
     * @param ImageInterface $img
     * @param int $width
     * @param int $height
     * @param string $bg_color
     * @param int $opacity
     * @return mixed
     */
    protected function padImage(ImageInterface $img, $width, $height, $bg_color, $opacity)
    {
        $size = $img->getSize();
        $x = $y = 0;
        if ($width > $size->getWidth()) {
            $x =  round(($width - $size->getWidth()) / 2);
        } elseif ($height > $size->getHeight()) {
            $y = round(($height - $size->getHeight()) / 2);
        }

        $palette = new RGB;
        $color = $palette->color($bg_color, $opacity);
        $image = Image::getImagine()->create(new Box($width, $height), $color);

        $pasteTo = new Point($x, $y);
        $image->paste($img, $pasteTo);

        return $image;
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
}
