<?php
namespace Aino\Model\Template;
use Aino\Model;

class Type extends Model
{

    protected $idColumnName = 'template_type_id';

    protected $apiUrl = '/api/template-types/';

    /**
     * Template type name
     * @var string
     */
    private $name;

    /**
     * Template type description
     * @var string
     */
    private $description;

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }
}