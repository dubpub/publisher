<?php namespace Dubpub\Publisher\Abstraction\Contracts;

interface IPublisherHandler
{
    /**
     * @param string $path
     * @param $type
     * @return $this
     */
    public function setPath($path, $type);

    /**
     * @param bool $autoCreate
     * @return bool
     */
    public function read($autoCreate = false);

    /**
     * @return bool
     */
    public function write();

    /**
     * @return bool
     */
    public function create();

    /**
     * @return bool
     */
    public function exists();

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @return array|string[]
     */
    public function getPackageNames();

    /**
     * @param string $packageName
     * @return bool
     */
    public function packageExists($packageName);

    /**
     * @param $packageName
     * @return array|string[]
     */
    public function getGroupsByPackageName($packageName);

    /**
     * @param $packageName
     * @param array $groups
     * @return $this
     */
    public function setPackageGroups($packageName, array $groups);

    /**
     * @return string
     */
    public function getComposerName();

    /**
     * @param IPublisherHandler $handler
     * @return $this
     */
    public function merge(IPublisherHandler $handler);
}
