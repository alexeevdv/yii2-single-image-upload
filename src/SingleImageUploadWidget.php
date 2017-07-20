<?php

namespace alexeevdv\image;

use kartik\file\FileInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class OneImageUploadWidget
 * @package alexeevdv\image
 */
class SingleImageUploadWidget extends FileInput
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $value  = $this->model ? $this->model->{$this->attribute} : $this->value;

        $this->pluginOptions = ArrayHelper::merge(
            [
                'layoutTemplates' => 'main2',
                'fileActionSettings' => [
                    'showDrag' => false,
                    'showUpload' => false,
                    'showRemove' => false,
                ],
                'showCaption' => false,
                'showUpload' => false,
                'initialPreview' => [
                    $value ? Url::to('/uploads/' . $$value) : null
                ],
                'initialPreviewAsData' => true,
            ],
            $this->pluginOptions
        );

        $this->options = ArrayHelper::merge(
            [
                'class' => ['single-image-upload-widget'],
            ],
            $this->options
        );

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        SingleImageUploadAsset::register($this->getView());

        $this->getView()->registerJs("
            $('#$this->id').on('fileclear', function(event) {
                $('#$this->id input[type=hidden]').val('');
            });
        ");

        $html = parent::run();
        if ($this->model) {
            $html .= Html::activeHiddenInput($this->model, $this->attribute);
        } else {
            $html .= Html::hiddenInput($this->name, $this->value);
        }
        return $html;
    }
}
