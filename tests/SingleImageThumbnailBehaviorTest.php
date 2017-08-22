<?php

use alexeevdv\image\SingleImageThumbnailBehavior;
use Codeception\Test\Unit;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use yii\base\DynamicModel;
use yii\base\Model;

/**
 * Class SingleImageThumbnailBehaviorTest
 */
class SingleImageThumbnailBehaviorTest extends Unit
{
    /**
     * @param int $width
     * @param int $height
     * @param int $thumbnailWidth
     * @param int $thumbnailHeight
     * @param string $mode
     * @dataProvider getThumbnailDataProvider
     */
    public function testGetThumbnail($width, $height, $thumbnailWidth, $thumbnailHeight, $mode)
    {
        $sourceSize = $width . 'x' . $height;
        $thumbnailSize = $thumbnailWidth . 'x' . $thumbnailHeight . '-' . $mode;

        $model = new DynamicModel([
            'image' => $sourceSize . '.jpg',
        ]);

        $model->attachBehavior('thumbnail', [
            'class' => SingleImageThumbnailBehavior::class,
            'sourcePath' => '@tests/_data',
            'destinationPath' => '@tests/_output',
            'baseUrl' => '/uploads',
            'thumbnails' => [
                $thumbnailSize => [
                    'width' => $thumbnailWidth,
                    'height' => $thumbnailHeight,
                    'mode' => $mode,
                ],
            ],
        ]);

        $url = $model->getThumbnail('image', $thumbnailSize);
        $this->assertEquals('/uploads/'. $thumbnailSize . '-' . $sourceSize . '.jpg', $url);

        list($imageWidth, $imageHeight) = getimagesize(Yii::getAlias('@tests/_output/' . $thumbnailSize . '-' . $sourceSize . '.jpg'));
        $this->assertEquals($thumbnailWidth, $imageWidth);
        $this->assertEquals($thumbnailHeight, $imageHeight);
    }

    /**
     *
     */
    public function testPlaceholder()
    {
        $model = new DynamicModel([
            'image' => null,
        ]);

        $model->attachBehavior('thumbnail', [
            'class' => SingleImageThumbnailBehavior::class,
            'sourcePath' => '@tests/_data',
            'destinationPath' => '@tests/_output',
            'baseUrl' => '/uploads',
            'thumbnails' => [
                'thumb' => [
                    'width' => 100,
                    'height' => 200,
                ],
            ],
        ]);

        $url = $model->getThumbnail('image', 'thumb');
        $this->assertEquals('http://placehold.it/100x200', $url);
    }

    /**
     * @return array
     */
    public function getThumbnailDataProvider()
    {
        return [
            [320, 200, 200, 150, ImageInterface::THUMBNAIL_OUTBOUND],
            // Should be surrounded by empty space on the top and the bottom
            [320, 200, 200, 150, ImageInterface::THUMBNAIL_INSET],
            // Should be resized and then cropped
            [320, 200, 400, 300, ImageInterface::THUMBNAIL_OUTBOUND],
            // Should be resized and then surrounded by empty space on the top and the bottom
            [320, 200, 400, 300, ImageInterface::THUMBNAIL_INSET],
        ];
    }
}
