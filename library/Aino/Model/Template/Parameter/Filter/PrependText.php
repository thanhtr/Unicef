<?php
namespace Aino\Model\Template\Parameter\Filter;
use Aino\Model\Template\Parameter\Filter;

class PrependText extends Filter
{
    public function apply($values)
    {
        $options = $this->getOptions();
        $prependText = isset($options['text'])?$options['text']:'';

        $values['element'][] = $values['value'] . $prependText;

        return;
    }
}
