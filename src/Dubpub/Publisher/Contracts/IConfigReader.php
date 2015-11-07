<?php namespace Dubpub\Publisher\Contracts;

interface IConfigReader
{
    /**
     * @param string $groupName
     * @return bool
     */
    public function groupExists($groupName);

    /**
     * Returns file groups.
     *
     * @return IConfigGroup[]
     */
    public function getGroups();

    /**
     * Returns file group.
     *
     * @param $groupName
     * @return IConfigGroup
     */
    public function getGroup($groupName);

    /**
     * Returns file groups by names.
     *
     * @param array $groupNames
     * @return IConfigGroup[]
     */
    public function getGroupByNames(array $groupNames);

    /**
     * Return config path.
     *
     * @return string
     */
    public function getPath();

    /**
     * Sets config path.
     *
     * @param $path
     * @return $this
     */
    public function setPath($path);

    /**
     * Parses config file.
     *
     * @return mixed
     */
    public function read();
}