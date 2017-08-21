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
            'image' => 'test1.jpg',
        ]);

        $model->attachBehavior('thumbnail', [
            'class' => SingleImageThumbnailBehavior::class,
            'sourcePath' => '@tests/_data',
            'destinationPath' => '@tests/_output',
            'baseUrl' => '/uploads',
            'thumbnails' => [
                'thumb' => [
                    'width' => 100,
                    'height' => 100,
                ],
            ],
        ]);

        $x = $model->getThumbnail('image', 'thumb');
        var_dump($x);
        die();
    }
}
