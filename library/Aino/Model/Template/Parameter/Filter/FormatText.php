<?php
namespace Aino\Model\Template\Parameter\Filter;
use Aino\Model\Template\Parameter\Filter;

class FormatText extends Filter
{
    public function apply($values)
    {
        $element = $values['element'];
        $value = $values['value'];

        $options = $this->getOptions();

        $lines = explode("\n", $value);

        if (count($lines) > 1) {
            $lineHeight = floatval(
                $options['lineHeight']?$options['lineHeight']:0
            );
            foreach($lines as $lineIndex => $line) {
                $tspan = $element->addChild('tspan', $line);
                $tspan->addAttribute('x', '0');
                $tspan->addAttribute('y', $lineIndex * $lineHeight);
            }
        } else {
            $element[] = $value;
        }

        return;
    }
}
