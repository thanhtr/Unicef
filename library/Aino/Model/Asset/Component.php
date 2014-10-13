<?php
namespace Aino\Model\Asset;
use Aino\Model\Asset;
use Aino\Model;
use Aino\Model\Set;
use Aino\Model\Template;

class Component extends Model
{
    const TYPE_FILTER = 'filter';

    const TYPE_VALUE = 'value';

    protected $idColumnName = 'component_id';

    protected $apiUrl = '/api/components/';

    /**
     * @var string
     */
    protected $description;

    /*
     * @var string
     */
    protected $name;

    /*
     * @var Aino_Model_Set
     */
    protected $filters;

    /*
     * @var Aino_Model_Set
     */
    protected $groups;

    public function __construct()
    {
        $this->filters = new Set();
        $this->groups = new Set();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return \Aino_Model_Asset_Component
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get component name
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
     * @return \Aino_Model_Asset_Component
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get component description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add group
     *
     * @param Aino_Model_Asset_Filter $filter
     * @return Aino_Model_Asset
     */
    public function addFilter(Template\Parameter\Filter $filter)
    {
        $this->filters->addItem($filter);
        return $this;
    }

    /**
     * Set groups
     *
     * @param array $componentArray
     * @return Aino_Model_Asset
     */
    public function setFilters($filtersArray) {

        foreach ($filtersArray as $filter) {
            if ($filter instanceof Template\Parameter\Filter) {
                $this->filters->addItem($filter);
            } else {
                $this->filters->addItem(
                    Template\Parameter\Filter::factory($filter)
                );
            }
        }

        return $this;
    }

    /**
     * Get groups
     *
     * @return \Aino_Model_Set
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Add group
     *
     * @param Aino_Model_Asset_Group $group
     * @return Aino_Model_Asset
     */
    public function addGroup(Asset\Group $group)
    {
        $this->groups->addItem($group);
        return $this;
    }

    /**
     * Set groups
     *
     * @param array $componentArray
     * @return Aino_Model_Asset
     */
    public function setGroups($groupsArray) {

        foreach ($groupsArray as $group) {
            if ($group instanceof Asset\Group) {
                $this->groups->addItem($group);
            } else {
                $this->groups->addItem(
                    Asset\Group::factory($group)
                );
            }
        }

        return $this;
    }

    /**
     * Get groups
     *
     * @return \Aino_Model_Set
     */
    public function getGroups()
    {
        return $this->groups;
    }

}
