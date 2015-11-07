<?php namespace Dubpub\Publisher\GroupTypes;

use Dubpub\Publisher\Contracts\IConfigGroup;

class Common implements IConfigGroup
{
    protected $name;

    protected $paths = [];

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param string[] $paths
     * @return $this
     */
    public function setPaths(array $paths = [])
    {
        $this->paths = $paths;
        return $this->paths;
    }
}