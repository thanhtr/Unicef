<?php
namespace Aino\Model;
use Aino\Model\Set;

/**
 * Template model
 */
class Asset extends Image
{
    protected $thumbnailPath = '/api/assets/thumbnail';

    protected $idColumnName = 'asset_id';

    protected $toArrayBlacklist = array('image', 'dpi');

    protected $apiUrl = '/api/assets/';

    protected $cachePrependStr = 'asset';

    /**
     * @var Aino_Model_Set
     */
    private $groups;

    /**
     * @var Aino_Model_Set
     */
    private $components;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $format;

    /**
     * @var integer
     */
    private $created;

    /**
     * @var \Imagick
     */
    private $image;

    /**
     * @var bool
     */
    private $ugc = false;

    /**
     * @var \Aino_Auth_Identity
     */
    private $user;

    public function __construct()
    {
        $this->groups = new Set();
        $this->components = new Set();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return \Aino_Model_Asset
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
     * @return \Aino_Model_Asset
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
     * Set format
     *
     * @param string $format
     * @return \Aino_Model_Asset
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Get format description
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set Created
     *
     * @param string $created
     * @return \Aino_Model_Asset
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
     * @return \Aino_Model_Asset
     */
    public function setUser($user)
    {
        if (!$user instanceof \Aino_Auth_Identity) {
            $user = new \Aino_Auth_Identity($user);
        }

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
     * Set path
     *
     * @param string $path
     * @return \Aino_Model_Asset
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath($absolute = false)
    {
        if ($absolute) {
            $filePath = realpath(APPLICATION_PATH . '/../data');
        } else {
            $filePath = '';
        }

        return $filePath . $this->path;
    }

    /**
     * Set ugc
     *
     * @param bool $ugc
     * @return \Aino_Model_Asset
     */
    public function setUgc($ugc)
    {
        $this->ugc = (bool) $ugc;
        return $this;
    }

    /**
     * Get ugc
     *
     * @return bool
     */
    public function getUgc()
    {
        if ($this->ugc) {
            return 1;
        }

        return 0;
    }

    /**
     * Get svg
     *
     * @return string
     */
    public function getImage()
    {
        if (!($this->image instanceof \Imagick)) {
            $image = $this->readCache();

            if ($image instanceof \Imagick) {
                $this->image = $image;
                return $image;
            }

            $filePath = $this->getPath(true);

            if (is_file($filePath)) {
                $this->image = new \Imagick($filePath);
            }

            $this->image = $this->applyFilters($this->image);
            $this->writeCache($this->image);
        }

        return $this->image;
    }

    /**
     * Add asset group
     *
     * @param Aino_Model_Asset_Group $assetGroup
     * @return Aino_Model_Asset
     */
    public function addGroup(Asset\Group $assetGroup)
    {
        $this->groups->addItem($assetGroup);
        return $this;
    }

    /**
     * Set asset groups
     *
     * @param array $groupArray
     * @return Aino_Model_Asset
     */
    public function setGroups($groupArray) {

        if (is_array($groupArray) || $groupArray instanceof Set) {
            foreach ($groupArray as $group) {
                if ($group instanceof Asset\Group) {
                    $this->groups->addItem($group);
                } else {
                    $this->groups->addItem(
                        Asset\Group::factory($group)
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Get groups
     *
     * @return \Aino_Model_Asset_Group
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add component
     *
     * @param Aino_Model_Asset_Component $component
     * @return Aino_Model_Asset
     */
    public function addComponent(Asset\Component $component)
    {
        $this->components->addItem($component);
        return $this;
    }

    /**
     * Set components
     *
     * @param array $componentArray
     * @return Aino_Model_Asset
     */
    public function setComponents($componentArray) {

        if ($componentArray instanceof Set) {
            $this->components = $componentArray;
            return $this;
        }

        foreach ($componentArray as $component) {
            if ($component instanceof Asset\Component) {
                $this->components->addItem($component);
            } else {
                $this->components->addItem(
                    Asset\Component::factory($component)
                );
            }
        }

        return $this;
    }

    /**
     * Get components
     *
     * @return \Aino_Model_Set
     */
    public function getComponents()
    {
        return $this->components;
    }

    public function grabFile($filePath)
    {
        if (is_file($filePath)) {
            $dir = substr(uniqid(), -2, 2);
            $path = $this->setPath('/' . $dir)->getPath(true);

            if (!is_dir($path)) {
                mkdir($path);
            }

            $path = '/' . $dir . '/' . uniqid();
            $this->setPath($path);

            if (copy($filePath, $this->getPath(true))) {
                unlink($filePath);
                return true;
            }

            return false;
        }
    }

    /**
     * @return string
     */
    protected function compileCacheKey()
    {
        $cacheKey = '';

        foreach ($this->getFilters() as $filter) {
            $cacheKey = sha1($filter->compileCacheKey() . $cacheKey);
        }

        if (!$cacheKey) {
            $cacheKey = sha1($this->getId());
        }

        return $cacheKey;
    }
}
