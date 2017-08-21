<?php

use alexeevdv\image\SingleImageThumbnailBehavior;
use Codeception\Test\Unit;
use yii\base\DynamicModel;
use yii\base\Model;

class SingleImageThumbnailBehaviorTest extends Unit
{
    public function testGetThumbnail()
    {
        $model = new DynamicModel([
            'image' => '320x200.jpg',
        ]);

        $model->attachBehavior('thumbnail', [
            'class' => SingleImageThumbnailBehavior::class,
            'sourcePath' => '@tests/_data',
            'destinationPath' => '@tests/_output',
            'baseUrl' => '/uploads',
            'thumbnails' => [
                '200x150' => [
                    'width' => 200,
                    'height' => 150,
                ],
            ],
        ]);

        $url = $model->getThumbnail('image', '200x150');
        $this->assertEquals('/uploads/200x150-320x200.jpg', $url);

        // TODO check generated image size

    }
}
