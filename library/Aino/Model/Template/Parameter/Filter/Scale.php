<?php
namespace Aino\Model\Template\Parameter\Filter;
use Aino\Model\Template\Parameter\Filter;

class Scale extends Filter
{
    public function apply($image)
    {
        $options = $this->getOptions();

        $width = isset($options['width'])?(string) $options['width']:0;
        $height = isset($options['height'])?(string) $options['height']:0;
        $bestfit = isset($options['bestfit'])?(bool) $options['bestfit']:false;

        $image->scaleImage($width, $height, $bestfit);

        return $image;
    }
}
