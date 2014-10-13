<?php
namespace Aino\Model\Template\Parameter\Filter\Nokia;

use Aino\Model\Template\Parameter\Filter;

class Price extends Filter
{
    public function apply($values)
    {
        $element = $values['element'];
        $value = $values['value'];
        $options = $this->getOptions();
        $superFontSize = isset($options['superFontSize'])?
            $options['superFontSize']:'25';

        $values = $this->explodeValue($value);
        if($values) {
            foreach ($element->children as $index => $child) {
                unset($element[$index]);
            }
            $element[0] = '';

            $currencyNode = $element->addChild('tspan', $values['currency']);
            $currencyNode->addAttribute('baseline-shift', 'super');
            $currencyNode->addAttribute('font-size', $superFontSize);

            $element->addChild('tspan', $values['wholes']);

            $fractionNode = $element->addChild('tspan', $values['fraction']);
            $fractionNode->addAttribute('baseline-shift', 'super');
            $fractionNode->addAttribute('font-size', $superFontSize);
        }

        return;
    }

    public function explodeValue($value)
    {
        $matches = array();

        if(preg_match_all('/([€$£])(\d+.)(\d+)/u', $value, $matches)) {
            return array(
                'currency' => $matches[1][0],
                'wholes' => $matches[2][0],
                'fraction' => $matches[3][0]
            );
        }
        return false;
    }
}
