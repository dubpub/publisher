<?php namespace Vgtrk\Publisher\Contracts;

interface IConfigGroup
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string[]
     */
    public function getPaths();

    /**
     * @param string[] $paths
     * @return $this
     */
    public function setPaths(array $paths = []);
}