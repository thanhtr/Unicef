<?php
namespace Aino\Model\Template\Parameter;
use Aino\Model;
use Aino\Model\Template\Parameter;

class Dependency extends Model
{
    /**
     * @var string
     */
    protected $idColumnName = 'on';

    /**
     * @var array
     */
    protected $toArrayBlacklist = array('url', 'modifiersFor');

    /**
     * @var array
     */
    protected $dependencyMap = array();

    public function addValueMapping($value, $modifiers) {
        $this->dependencyMap[$value] = $modifiers;
    }

    static public function parseDependency($parameterXML)
    {
        $dependency = self::factory($parameterXML);

        $cases = $parameterXML->xpath('./aino:when');

        foreach ($cases as $caseXML) {
            $value = (string) $caseXML['value'];

            $replacementValues = $caseXML->xpath('./aino:value');
            $replacementValue = null;
            if (count($replacementValues) > 0) {
                $replacementValue = (string) $replacementValues[0];
            }

            $modifiers = array(
                'filters' => Parameter::parseFilters($caseXML),
                'options' => Parameter::parseSelectOptions($caseXML),
                'value' => $replacementValue
            );

            $dependency->addValueMapping($value, $modifiers);
        }

        return $dependency;
    }

    public function getDependencyMap()
    {
        return $this->dependencyMap;
    }

    public function hasModifiersFor($value)
    {
        return isset($this->dependencyMap[$value]);
    }

    public function getModifiersFor($value)
    {
        if (isset($this->dependencyMap[$value])) {
            return $this->dependencyMap[$value];
        }

        return array(
            'filters' => array(),
            'options' => array(),
            'value' => null
        );
    }

}