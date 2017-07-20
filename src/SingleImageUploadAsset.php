<?php

namespace alexeevdv\image;

use yii\web\AssetBundle;

/**
 * Class SingleImageUploadAsset
 * @package alexeevdv\image
 */
class SingleImageUploadAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@vendor/alexeevdv/yii2-single-image-upload/assets';

    /**
     * @inheritdoc
     */
    public $css = [
        'css/styles.css',
    ];
}
