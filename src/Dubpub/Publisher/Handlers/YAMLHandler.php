<?php namespace Dubpub\Publisher\Handlers;

use Dubpub\Publisher\Contracts\IPublisherHandler;

class PHPHandler implements IPublisherHandler
{
    /**
     * @var string
     */
    protected $path;
    /**
     * @var mixed
     */
    protected $data;
    /**
     * @var string
     */
    protected $composerName;

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @var string $path
     * @return $this
     * @throws \Exception
     */
    public function setPath($path)
    {
        $this->path = realpath($path);

        if (!$this->path) {
            throw new \Exception('Path doesn\'t exist: '. $path);
        }

        if (!file_exists($composerPath = $this->path . '/composer.json')) {
            throw new \LogicException('Publisher file must be located same folder, as composer.json. Path: '.$path);
        }

        $this->composerName = json_decode(file_get_contents($composerPath))->name;

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

        $this->data = include $this->path . '/.publisher.php';

        if (is_array($this->data)) {
            $this->data = (object) $this->data;
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function write()
    {
        return file_put_contents(
            $this->path . '/.publisher.php', '<?php return '.var_export_braces((array)$this->data).';'
        );
    }

    /**
     * @return bool
     */
    public function create()
    {
        $data = [$this->composerName => []];
        return file_put_contents($this->path . '/.publisher.php', '<?php return '.var_export($data, true).';');
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->path . '/.publisher.php');
    }

    /**
     * @return mixed
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
        return array_keys((array) $this->data);
    }

    /**
     * @param $vendorName
     * @return array|\string[]
     */
    public function getPackagePathGroups($vendorName)
    {
        return $this->data->{$vendorName};
    }

    public function setPackagePaths($packageName, array $paths)
    {
        $this->data->{$packageName} = $paths;

        return $this;
    }

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
        $newData = (array) $handler->getPackagePathGroups($newComposerName = $handler->getComposerName());

        $this->setPackagePaths($newComposerName, $newData);

        return $this;
    }
}