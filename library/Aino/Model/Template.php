<?php
namespace Aino\Model;
use Aino\Model\Template;
use Aino\Model\Template\Request;

/**
 * Template model
 */
class Template extends Image
{
    protected $thumbnailPath = '/api/templates/thumbnail';

    protected $idColumnName = 'template_id';

    protected $toArrayBlacklist = array('svg', 'image', 'dpi');

    protected $apiUrl = '/api/templates/';

    static $ComponentMapper = null;

    /**
     * @var \Aino_Model_Set
     */
    protected $parameters = null;

    /**
     * @var \Imagick
     */
    protected $image = null;

    /**
     * @var \Aino\Model\Set
     */
    private $types;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $created;

    /**
     * @var integer
     */
    private $modified;

    /**
     * @var \Aino_Auth_Identity
     */
    private $user;

    /**
     * @var \SimpleXMLElement
     */
    private $svg;

    public function __construct()
    {
        $this->types = new Set();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return \Aino_Model_Template
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get template name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return \Aino_Model_Template
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get template description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set Created
     *
     * @param string $created
     * @return \Aino_Model_Template
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get created
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set User
     *
     * @param \Aino_Auth_Identity $user
     * @return \Aino_Model_Template
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get User
     *
     * @return \Aino_Auth_Identity
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set Modified
     *
     * @param string $modified
     * @return \Aino_Model_Template
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * Get modified
     *
     * @return string
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set Svg xml string
     *
     * @param string $svgXml
     * @return \Aino_Model_Template
     */
    public function setSvg($svg)
    {
        if ($svg && !($svg instanceof SimpleXMLElement)) {
            try {
                $svg = simplexml_load_string($svg);
            } catch (Exception $e) {
                throw new Zend_Exception('Unable to parse SVG', 505);
            }
        }

        if (!$svg) {
            $svg = new SimpleXMLElement('<?xml version="1.0"?>
                <svg xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink"
                    width="1"
                    height="1"></svg>');

        }

        $svg->registerXPathNamespace(
            'aino',
            'http://luxus.fi/aino/parameters/1.0'
        );
        $svg->registerXPathNamespace(
            'xlink',
            'http://www.w3.org/1999/xlink'
        );
        $svg->registerXPathNamespace(
            'aino',
            'http://luxus.fi/aino/parameters/1.0'
        );

        $this->svg = $svg;

        return $this;
    }

    /**
     * Get svg
     *
     * @return string|SimpleXMLElement
     */
    public function getSvg($asSimpleXML = true)
    {
        if ($this->svg instanceof \SimpleXMLElement) {
            if ($asSimpleXML) {
               return $this->svg;
            }

            return $this->svg->asXML();
        }

        return '';
    }

    /**
     * Add template type
     *
     * @param Aino\Model\Template\Type $templateType
     * @return Aino\Model\Template
     */
    public function addType(Template\Type $templateType) {
        $this->types->addItem($templateType);

        return $this;
    }

    /**
     * Set template types
     * @param array|Aino\Model\Set $type
     * @return Aino\Model\Template
     */
    public function setTypes($typesArray)
    {
        foreach ($typesArray as $type) {
            if ($type instanceof Aino\Model\Template\Type) {
                $this->types->addItem($type);
            } else {
                $this->types->addItem(
                    Aino\Model\Template\Type::factory($type)
                );
            }
        }

        return $this;
    }

    /**
     * Get types
     *
     * @return Aino\Model\Set
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Set specified params
     * @return Aino\Model\Template
     */
    public function setParameters(Set $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Get specified params
     * @return Aino\Model\Set
     */
    public function getParameters()
    {
        if (is_null($this->parameters)) {
            $this->parameters =  new Set();
            $svg = $this->getSvg();

            if ($svg) {
                $this->setParameters($this->parseParameters($svg));
            }
        }

        return $this->parameters;
    }

    /**
     * Get specified params
     * @return Aino\Model\Set
     */
    protected function parseParameters($xml)
    {
        $parametersXML = $xml->xpath('//aino:parameters/aino:parameter');
        $parameters = new Set();
        if (is_array($parametersXML)) {
            foreach ($parametersXML as $parameterXML) {

                $className =
                    'Aino\\Model\\Template\\Parameter\\Type\\' .
                    ucfirst($parameterXML['type']);

                if (class_exists($className)) {
                    $parameter = $className::parseParameter($parameterXML);
                    $parameter->setTemplate($this);

                    $element = $this->findElementById(
                        $parameter->getElementId()
                    );

                    if ($element) {
                        $parameter->setElement($element);
                        $parameters->addItem($parameter);
                    }
                }
            }
        }

        return $parameters;
    }

    protected function normalizeRequest($parameters)
    {
        $requestParams = new Set();
        foreach ($parameters as $paramName => $parameter) {
            if (is_string($paramName)) {
                $parameter = array(
                    'id' => $paramName,
                    'value' => $parameter
                );
            }

            $requestParams->addItem(Template\Request::factory($parameter));
        }

        return $requestParams;
    }

    protected function applyComponents($templateParam, $requestParams)
    {
        if (self::$ComponentMapper === null) {
            self::$ComponentMapper = new \Api_Model_Mapper_Component();
        }

        $component = $templateParam->getComponent();
        $componentName = $component['name'];
        $parentId = $component['parentId'];

        $parent = $requestParams->findById($parentId);

        if ($parent) {
            $components = self::$ComponentMapper->findAllForAsset(
                $parent->getValue()
            );

            $component = $components->findBy(
                'name',
                $componentName
            );

            if ($component) {
                $templateParam->addFilters(
                    $component->getFilters(),
                    true
                );
            }
        }
    }

    /**
     * Setup parameters
     * @param array $parameters
     * @return \Aino_Model_Template
     */
    public function populateParameters($parameters = array())
    {
        $requestParams = $this->normalizeRequest($parameters);
        $templateParams = $this->getParameters();

        $copyFromParams = array();

        foreach ($templateParams as $templateParam) {
            if ($templateParam->isComponent()) {
                $this->applyComponents($templateParam, $requestParams);
            }

            foreach ($templateParam->getDependencies() as $dependency) {

                $parentRequestParam =
                    $requestParams->findById($dependency->getId());

                if ($parentRequestParam) {

                    $modifiers = $dependency->getModifiersFor(
                        $parentRequestParam->getValue()
                    );

                    $templateParam->addFilters($modifiers['filters']);

                    if ($modifiers['value'] !== null) {
                        $requestParam =
                            $requestParams->findById($templateParam->getId());

                        if ($requestParam) {
                            $requestParam->setValue($modifiers['value']);
                        } else {
                            $requestParams->addItem(
                                Request::factory(
                                    array(
                                        'id' => $templateParam->getId(),
                                        'value' => $modifiers['value']
                                    )
                                )
                            );
                        }
                    }
                }

            }

            if ($templateParam->getCopyFrom()) {
                $copyFromParams[] = $templateParam;
            }
        }

        foreach ($copyFromParams as $copyParam) {
            $requestParam = $requestParams->findById(
                $copyParam->getCopyFrom()
            );

            $templateParam = $templateParams->findById(
                $copyParam->getCopyFrom()
            );

            if ($requestParam) {
                $copyParam->setValue($requestParam->getValue());
            } else if ($templateParam) {
                $copyParam->setValue($templateParam->getValue());
            }
        }

        foreach ($requestParams as $parameter) {
            $templateParameter = $templateParams->findById($parameter->getId());
            $value = $parameter->getValue();

            if ($templateParameter && $value) {
                $templateParameter
                    ->addFilters($parameter->getFilters())
                    ->setValue($value);
            }
        }

        return $this;
    }

    /**
     * @return \Imagick
     */
    public function getImage()
    {
        if (is_null($this->image)) {
            $image = new \Imagick();
            $image->setBackgroundColor('none');
            $image->setFormat('SVG');
            $image->readImageBlob($this->getSvg(false));
            $dpi = $this->getDpi();
            $image->setimageresolution($dpi, $dpi);

            $this->image = $image;
        }

        return $this->image;
    }

    public function removeDraftElements()
    {
        $svg = $this->getSvg();

        $draftElements = $svg->xpath('*[@aino:draftElement]');
        foreach ($draftElements as $draftElement) {
            $draftElement = dom_import_simplexml($draftElement);
            $draftElement->parentNode->removeChild($draftElement);
        }

        return $this;
    }

    /**
     * Convert image
     * @param string $format
     * @param array $options
     * @return string|\Imagick
     */
    public function convert(
        $format = self::FORMAT_SVG,
        $options = array()
    ) {
        if ($format == self::FORMAT_SVG) {
            return $this->getSvg(false);
        }

        return parent::convert($format, $options);
    }

    /**
     * Find a simpleXmlElement by id
     * @param string $elementId
     * @return \SimpleXMLElement
     */
    protected function findElementById($elementId)
    {
        $elements = $this->getSvg()->xpath(
            '//*[@id="' . $elementId . '"]'
        );

        if (count($elements) == 1) {
            return $elements[0];
        }

        return null;
    }

    protected function compileCacheKey()
    {
        return '';
    }
}
