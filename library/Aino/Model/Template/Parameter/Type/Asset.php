<?php
namespace Aino\Model\Template\Parameter\Type;
use Aino\Model\Template\Parameter;

class Asset extends Parameter
{
    static protected $assetMapper = null;

    protected $assetGroups;

    static public function parseParameter($parameterXML)
    {
        $parameter = self::factory($parameterXML->attributes());
        $parameter
            ->setDependencies(
                self::parseDependencies($parameterXML)
            )
            ->setFilters(
                self::parseFilters($parameterXML)
            )
            ->setComponent(
                self::parseComponent($parameterXML)
            );

        return $parameter;
    }

    public function setAssetGroups($assetGroups)
    {
        $this->assetGroups = $assetGroups;
        return $this;
    }

    public function getAssetGroups()
    {
        return $this->assetGroups;
    }

    public function setValue($value)
    {
        $attributes = $this->getElement()->attributes(
            'http://www.w3.org/1999/xlink'
        );

        $value = $this->prepareValue($value);

        $attributes['href'] = $value;

        return $this;
    }

    public function getValue()
    {
        $attributes = $this->getElement()->attributes(
            'http://www.w3.org/1999/xlink'
        );

        return $attributes['href'];
    }

    protected function prepareValue($value)
    {
        if (self::$assetMapper === null) {
            self::$assetMapper = new \Api_Model_Mapper_Asset();
        }

        $asset = self::$assetMapper->find((string) $value);

        if ($asset) {
            $asset->setUseCache($this->getTemplate()->useCache());
            $asset->setFilters($this->getFilters());
            $this->applyAssosiativeAttributes($asset);
            $value = $asset->compileBase64String();
        }

        return $value;
    }

    protected function applyAssosiativeAttributes($asset)
    {
        $image = $asset->getImage();
        $attributes = $this->getElement()->attributes();
        $attributes['width'] = $image->getImageWidth();
        $attributes['height'] = $image->getImageHeight();
    }
}
