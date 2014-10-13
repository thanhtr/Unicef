<?php
namespace Aino\Model\Template\Parameter\Filter\Uni;

use Aino\Model\Template\Parameter\Filter;

class LogoScale extends Filter
{
    /**
     *
     * @param \Imagick $image
     * @return \Imagick
     */
    public function apply($image)
    {
        $options = $this->getOptions();
        $targetWidth = isset($options['width'])?(string) $options['width']:0;
        $targetHeight = isset($options['height'])?(string) $options['height']:0;

        $image->scaleImage($targetWidth, $targetHeight, true);

        $width = $image->getimagewidth();
        $height = $image->getimageheight();
        $sideLength = $width>$height?$width:$height;
        $image->extentimage(
            $targetWidth,
            $targetHeight,
            -1 * ($targetWidth-$width),
            -1 * ($targetHeight-$height)/2
        );

        return $image;
    }
}
