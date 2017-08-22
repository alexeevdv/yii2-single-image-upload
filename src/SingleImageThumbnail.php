<?php

namespace alexeevdv\image;

use Imagine\Image\ImageInterface;
use yii\base\InvalidConfigException;

/**
 * Class SingleImageThumbnail
 * @package alexeevdv\image
 */
class SingleImageThumbnail
{
    const MODE_INSET = ImageInterface::THUMBNAIL_INSET;
    const MODE_OUTBOUND = ImageInterface::THUMBNAIL_OUTBOUND;

    /**
     * @var int
     */
    private $_width;

    /**
     * @var integer
     */
    private $_height;

    /**
     * @var string
     */
    private $_mode;

    /**
     * @var string
     */
    private $_backgroundColor;

    /**
     * @var integer
     */
    private $_backgroundOpacity;

    /**
     * SingleImageThumbnail constructor.
     * @param array $options
     * @throws InvalidConfigException
     */
    public function __construct(array $options = [])
    {
        if (!isset($options['width'])) {
            throw new InvalidConfigException('`width` is required');
        }
        if (!isset($options['height'])) {
            throw new InvalidConfigException('`height` is required');
        }

        $this->_width = $options['width'];
        $this->_height = $options['height'];
        $this->_mode = isset($options['mode']) ? $options['mode'] : self::MODE_OUTBOUND;
        $this->_backgroundColor = isset($options['bg_color']) ? $options['bg_color'] : '#fff';
        $this->_backgroundOpacity = isset($options['bg_alpha']) ? $options['bg_alpha'] : 100;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->_backgroundColor;
    }

    /**
     * @return int
     */
    public function getBackgroundOpacity()
    {
        return $this->_backgroundOpacity;
    }
}
