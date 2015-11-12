<?php
namespace Dubpub\Publisher\Handlers;
use Dubpub\Publisher\Contracts\IPublisherHandler;

/**
 * Created by PhpStorm.
 * User: madman
 * Date: 11.11.15
 * Time: 23:22
 */
abstract class AHandler implements IPublisherHandler
{
    /**
     * @var string
     */
    protected $path;
    /**
     * @var mixed
     */
    protected $data = [];
    /**
     * @var string
     */
    protected $composerName;
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @param $filePath
     * @return array
     */
    abstract public function readData($filePath);

    /**
     * @param $filePath
     * @param array $data
     * @return bool
     */
    abstract public function writeData($filePath, array $data);

    /**
     * @var string $path
     * @param $type
     * @return $this
     * @throws \Exception
     */
    public function setPath($path, $type)
    {
        $this->path = realpath($path);

        $composerPath = $this->path . DIRECTORY_SEPARATOR . 'composer.json';

        /*if (!$this->path) {
            throw new \Exception('Path does not exist: '. $path);
        }

        if (!file_exists($composerPath)) {
            throw new \LogicException('Publisher file must be located same folder, as composer.json. Path: ' . $path);
        }*/

        $this->composerName = json_decode(file_get_contents($composerPath))->name;

        $this->filePath = $this->path . DIRECTORY_SEPARATOR . '.publisher.' . $type;

        return $this;
    }

    /**
     * @param bool $autoCreate
     * @return bool
     */
    public function read($autoCreate = false)
    {
        if (!$this->exists()) {
            if (!$autoCreate) {
                return false;
            } else {
                $this->create();
            }
        }

        $this->data = (array) $this->readData($this->filePath);

        return true;
    }

    /**
     * @return bool
     */
    public function write()
    {
        return $this->writeData($this->filePath, $this->data);
    }

    /**
     * @return bool
     */
    public function create()
    {
        return $this->writeData($this->filePath, [$this->composerName => []]);
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->filePath);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array|string[]
     */
    public function getPackageNames()
    {
        return array_keys($this->data);
    }

    /**
     * @param $vendorName
     * @return array|string[]
     */
    public function getPackagePathGroups($vendorName)
    {
        return $this->data[$vendorName];
    }

    /**
     * @param $packageName
     * @param array $paths
     * @return $this
     */
    public function setPackagePaths($packageName, array $paths)
    {
        $this->data[$packageName] = $paths;

        return $this;
    }

    /**
     * @return string
     */
    public function getComposerName()
    {
        return $this->composerName;
    }

    /**
     * @param IPublisherHandler $handler
     * @return $this
     */
    public function merge(IPublisherHandler $handler)
    {
        $newData = $handler->getPackagePathGroups($newComposerName = $handler->getComposerName());

        $this->setPackagePaths($newComposerName, $newData);

        return $this;
    }
}