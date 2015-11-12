<?php namespace Dubpub\Publisher\Contracts;

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
     * @param $vendorName
     * @return array|string[]
     */
    public function getPackagePathGroups($vendorName);

    /**
     * @param $packageName
     * @param array $paths
     * @return $this
     */
    public function setPackagePaths($packageName, array $paths);

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
