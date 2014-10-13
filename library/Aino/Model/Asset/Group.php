<?php
namespace Aino\Model\Asset;
use Aino\Model;

class Group extends Model
{

    protected $idColumnName = 'asset_group_id';

    protected $apiUrl = '/api/asset-groups/';

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