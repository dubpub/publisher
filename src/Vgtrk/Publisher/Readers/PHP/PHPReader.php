<?php namespace Vgtrk\Publisher\Readers\PHP;

use Vgtrk\Publisher\Contracts\IConfigGroup;
use Vgtrk\Publisher\Contracts\IConfigReader;
use Vgtrk\Publisher\GroupTypes\Common;

class PHPReader implements IConfigReader
{
    /**
     * @var array
     */

    private $config;
    /**
     * Config path.
     *
     * @var string config path
     */
    protected $path;
    /**
     * @var IConfigGroup[]|Common[]
     */
    protected $groups;

    public function groupExists($groupName)
    {
        return array_key_exists($groupName, $this->groups);
    }

    /**
     * Returns config groups.
     *
     * @return IConfigGroup[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param $groupName
     * @return IConfigGroup
     */
    public function getGroup($groupName)
    {
        if (!$this->groupExists($groupName)) {
            throw new \InvalidArgumentException('Unknown group: '.$groupName);
        }

        return $this->groups[$groupName];
    }

    public function getGroupByNames(array $groupNames)
    {
        $result = [];

        foreach ($groupNames as $groupName) {
            $result[$groupName] = $this->getGroup($groupName);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Parses config file.
     *
     * @return $this
     */
    public function read()
    {
        $this->config = include $this->getPath();

        if (!is_array($this->config)) {
            throw new \LogicException($this->getPath() . ' script must return array.');
        }

        foreach ($this->config as $pathGroupName => $pathGroup) {
            $configGroup = new Common();
            $configGroup->setName($pathGroupName);
            $configGroup->setPaths($pathGroup);
            $this->groups[$pathGroupName] = $configGroup;
        }

        return $this;
    }
}