<?php namespace Dubpub\Publisher\Readers\Yaml;

use Symfony\Component\Yaml\Parser;
use Dubpub\Publisher\Contracts\IConfigGroup;
use Dubpub\Publisher\Contracts\IConfigReader;
use Dubpub\Publisher\GroupTypes\Common;

/**
 * Created by PhpStorm.
 * User: madman
 * Date: 01.11.15
 * Time: 22:36
 */
class YAMLReader implements IConfigReader
{
    /** @var  string */
    protected $path;
    /**
     * @var Parser
     */
    protected $yamlParser;
    /**
     * @var IConfigGroup[]|Common
     */
    protected $groups;

    public function __construct()
    {
        $this->yamlParser = new Parser();
    }

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
     * Return config path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets config path.
     *
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
     * @return mixed
     */
    public function read()
    {
        $readValue = $this->yamlParser->parse(file_get_contents($this->getPath()));

        foreach ($readValue as $group => $paths) {
            $this->groups[$group] = new Common();
            $this->groups[$group]->setName($group)->setPaths($paths);
        }

        return $this;
    }
}