## Installation

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run

```bash
$ composer require alexeevdv/yii2-single-image-upload "^1.0"
```

or add

```
"alexeevdv/yii2-single-image-upload": "^1.0"
```

to the ```require``` section of your `composer.json` file.

## Usage

```php
echo $form->field($model, 'image')->widget(alexeevdv\image\SingleImageUploadWidget::class);
```
