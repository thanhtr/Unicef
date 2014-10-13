<?php
namespace Aino\Model;
use Aino\Model;
use Aino\Model\Set;

abstract class Image extends Model
{
    const FORMAT_SVG = 'svg';

    const FORMAT_PNG = 'png';

    const FORMAT_JPG = 'jpg';

    const FORMAT_PDF = 'pdf';

    const THUMBNAIL_SMALL = 'small';

    const THUMBNAIL_MEDIUM = 'medium';

    const THUMBNAIL_LARGE = 'large';

    const THUMBNAIL_EXTENSION = '.png';

    const CACHED_IMAGE_FORMAT = 'png';

    protected $thumbnailPath = '';

    protected $cachePrependStr = '';

    protected $useCache = true;

    protected $toArrayBlacklist = array('image', 'dpi');

    /**
     * @var integer
     */
    protected $dpi = 72;

    /**
     * @var \Aino_Model_Set
     */
    protected $filters;

    protected function generateThumbnailPaths()
    {
        $id = $this->getId();

        return array(
            self::THUMBNAIL_LARGE =>
                implode(
                    '/',
                    array(
                        $this->thumbnailPath,
                        $id,
                        'size',
                        self::THUMBNAIL_LARGE . self::THUMBNAIL_EXTENSION
                    )
                ),
            self::THUMBNAIL_MEDIUM =>
                implode(
                    '/',
                    array(
                        $this->thumbnailPath,
                        $id,
                        'size',
                        self::THUMBNAIL_MEDIUM . self::THUMBNAIL_EXTENSION
                    )
                ),
            self::THUMBNAIL_SMALL =>
                implode(
                    '/',
                    array(
                        $this->thumbnailPath,
                        $id,
                        'size',
                        self::THUMBNAIL_SMALL . self::THUMBNAIL_EXTENSION
                    )
                )
        );
    }

    public function getThumbnails()
    {
        $serverUrl = self::compileServerUrl();

        $paths = $this->generateThumbnailPaths();

        return array(
            self::THUMBNAIL_LARGE =>
                $serverUrl . $paths[self::THUMBNAIL_LARGE],
            self::THUMBNAIL_MEDIUM =>
                $serverUrl . $paths[self::THUMBNAIL_MEDIUM],
            self::THUMBNAIL_SMALL =>
                $serverUrl . $paths[self::THUMBNAIL_SMALL],
        );

    }

    /**
     * Clear thumbnail cache
     *
     * @return \Aino_Model_ImageAbstract
     */
    public function clearThumbnailCache()
    {
        $paths = $this->generateThumbnailPaths();

        foreach ($paths as $path) {
            $path = APPLICATION_PATH . '/../public' . $path;

            if (is_file($path)) {
                unlink($path);
            }
        }

        return $this;
    }

    /**
     * Cache thumbnail
     *
     * @param string $size
     * @param \Imagick $image
     * @return \Aino_Model_ImageAbstract
     */
    protected function cacheThumbnail($size, $image)
    {
        $paths = $this->generateThumbnailPaths();

        if (isset($paths[$size])) {
            $path = APPLICATION_PATH . '/../public' . $paths[$size];
            $pathInfo = pathinfo($path);
            $dirPath = $pathInfo['dirname'];

            try {
                if (!is_dir($dirPath)) {
                    mkdir($dirPath, '0493', true);
                }
                $image->writeImage($path);
            } catch (Exception $e) {
                //@TODO log failure
            }
        }

        return $image;
    }

    /**
     * Get thumbnail
     *
     * @param string $size
     * @return \Imagick
     */
    public function thumbnail($size = self::THUMBNAIL_SMALL)
    {
        $image = $this->getImage();
        $image->setformat('PNG');

        $width = $image->getimagewidth();
        $height = $image->getimageheight();
        $sideLength = $width>$height?$width:$height;
        $image->extentimage(
            $sideLength,
            $sideLength,
            -1 * ($sideLength-$width)/2,
            -1 * ($sideLength-$height)/2
        );

        switch ($size) {
            case self::THUMBNAIL_LARGE:
                $image->thumbnailImage(200, 200);
                $this->cacheThumbnail(self::THUMBNAIL_LARGE, $image);
                break;
            case self::THUMBNAIL_MEDIUM:
                $image->thumbnailImage(100, 100);
                $this->cacheThumbnail(self::THUMBNAIL_MEDIUM, $image);
                break;
            default:
                $image->thumbnailImage(50, 50);
                $this->cacheThumbnail(self::THUMBNAIL_SMALL, $image);
                break;
        }

        return $image;
    }

    /**
     * @return \Imagick
     */
    abstract public function getImage();

    public function compileBase64String()
    {
        $image = $this->getImage();
        $image->setformat('PNG');

        $data = 'data:image/png;base64,';

        return $data . base64_encode($image->getimageblob());
    }

    /**
     * Convert image
     * @param string $format
     * @param array $options
     * @return string|\Imagick
     */
    public function convert(
        $format = Aino\Model\Image::FORMAT_PNG
    ) {
        $image = $this->getImage();

        if ($image) {
            switch ($format) {
                case self::FORMAT_PDF:
                    $image->setformat('PDF');
                    break;
                case self::FORMAT_PNG:
                    $image->setformat('PNG');
                    break;
                case self::FORMAT_JPG:
                    $image->setformat('JPEG');
                    break;
                case self::FORMAT_BASE64:
                    $image = $this->compileBase64String();
                    break;
                default:
                    break;
            }
        }

        return $image;
    }

    /**
     * @param \Aino_Model_Set $filters
     * @return \Aino_Model_ImageAbstract
     */
    public function setFilters($filters)
    {
        if ($this->filters instanceof Set) {
            $this->filters->clearAll();
        } else {
            $this->filters = new Set();
        }

        foreach ($filters as $filter) {
            if ($filter instanceof Template\Parameter\Filter) {
                $this->filters->addItem($filter);
            }
        }

        return $this;
    }

    /**
     * @return \Aino_Model_Set
     */
    public function getFilters()
    {
        if (!($this->filters instanceof Set)) {
            $this->filters = new Set();
        }

        return $this->filters;
    }

    /**
     * @param \Imagick $image
     * @return \Imagick
     */
    protected function applyFilters($image)
    {
        foreach ($this->getFilters() as $filter) {

            if (false == $filter->applied) {
                $image = $filter->apply($image);
            }
        }

        return $image;
    }

    /**
     * @return string
     */
    abstract protected function compileCacheKey();

    /**
     * @param string $cacheKey
     * @return string
     */
    protected function compileCachePath()
    {
        $cacheKey = $this->compileCacheKey();

        $dirNameLen = 5;

        $path = $cacheKey . '.' . strtolower(self::CACHED_IMAGE_FORMAT);
        $path = $this->cachePrependStr . '_' . $this->getId() . '/' . $path;

        return APPLICATION_PATH . '/../data/cache/' . $path;
    }

    public function clearCache()
    {
        $path =
            APPLICATION_PATH . '/../data/cache/' .
            $this->cachePrependStr . '_' . $this->getId() . '/';

        if (is_dir($path)) {
            try {
                foreach(glob($path . '/*') as $file) {
                    unlink($file);
                }

                rmdir($path);
            } catch (Exception $e) {
                //@todo unable to clear the cache
            }
        }

        return $this;
    }

    /**
     * @param string $cacheKey
     * @return \Imagick
     */
    protected function readCache()
    {
        if (false == $this->useCache()) {
            return null;
        }
        $path = $this->compileCachePath();
        $image = null;

        if (is_file($path)) {
            $image = new \Imagick();
            $image->setformat(self::CACHED_IMAGE_FORMAT);

            try {
                $image->readImage($path);

                foreach ($this->getFilters() as $filter) {
                    $filter->applied = true;
                }
            } catch (Exception $e) {
                //@todo Log failed to read cached image
            }
        }

        return $image;
    }

    /**
     *
     * @param string $cacheKey
     * @param \Imagick $image
     * @return \Aino_Model_ImageAbstract
     */
    protected function writeCache($image)
    {
        if (false == $this->useCache()) {
            return $this;
        }
        $path = $this->compileCachePath();

        if (!is_file($path)) {
            $dir = dirname($path);

            try {
                if(!is_dir($dir)) {
                    mkdir($dir, '0493', true);
                }

                $imageClone = clone $image;
                $imageClone->setformat(self::CACHED_IMAGE_FORMAT);
                $imageClone->writeimage($path);
            } catch (Exception $e) {
                //@TODO Log failed to write cache
            }
        }

        return $this;
    }

    /**
     * Set dpi
     * @param integer $dpi
     * @return \Aino_Model_ImageAbstract
     */
    public function setDpi($dpi)
    {
        $this->dpi = (int) $dpi;
        return $this;
    }

    /**
     * Get dpi
     * @return integer
     */
    public function getDpi()
    {
        return (int) $this->dpi;
    }

    public function setUseCache($useCache)
    {
        $this->useCache = (bool) $useCache;
        return $this;
    }

    public function useCache()
    {
        return $this->useCache;
    }
}