<?php
namespace Aino\Model\Template\Parameter\Filter;
use Aino\Model\Template\Parameter\Filter;

class DistortPerspective extends Filter
{
    public function apply($image)
    {
        $options = $this->getOptions();
        $args = isset($options['args'])?$options['args']:array();
        $bestfit = isset($options['bestfit'])?$options['bestfit']:false;

        $width = isset($options['width'])?$options['width']:0;
        $height = isset($options['height'])?$options['height']:0;

        if (count($args) == 8) {
            $image->borderImage(new \ImagickPixel("none"), 1, 1);
            $image->setImageVirtualPixelMethod(
                \Imagick::VIRTUALPIXELMETHOD_TRANSPARENT
            );
            $image->scaleImage($width, $height, false);

            $arguments = array();
            //Left top
            $arguments[] = 0;
            $arguments[] = 0;
            $arguments[] = $args[0];
            $arguments[] = $args[1];

            //Right top
            $arguments[] = $width;
            $arguments[] = 0;
            $arguments[] = $args[2];
            $arguments[] = $args[3];

            //Right Bottom
            $arguments[] = $width;
            $arguments[] = $height;
            $arguments[] = $args[4];
            $arguments[] = $args[5];

            //Right Bottom
            $arguments[] = 0;
            $arguments[] = $height;
            $arguments[] = $args[6];
            $arguments[] = $args[7];

            $image->distortImage(
                \Imagick::DISTORTION_PERSPECTIVE,
                $arguments,
                $bestfit
            );
        } else {
            //@TODO: log wrong args array size
        }

        return $image;
    }
}
