<?php
namespace Aino\Model\Template\Parameter\Filter;
use Aino\Model\Template\Parameter\Filter;

class DistortPolynomial extends Filter
{
    public function apply($image)
    {
        $options = $this->getOptions();

        $args = isset($options['args'])?$options['args']:array();
        $width = isset($options['width'])?$options['width']:0;
        $height = isset($options['height'])?$options['height']:0;
        $bestfit = isset($options['bestfit'])?$options['bestfit']:true;

        $image->borderImage(new \ImagickPixel("none"), 1, 1);
        $image->setImageVirtualPixelMethod(
            \Imagick::VIRTUALPIXELMETHOD_TRANSPARENT
        );

        if ($width || $height) {
            $image->scaleImage($width, $height, false);
        }

        $image->distortImage(
            \Imagick::DISTORTION_POLYNOMIAL,
            $args,
            $bestfit
        );

        return $image;
    }
}
