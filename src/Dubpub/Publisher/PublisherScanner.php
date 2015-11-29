<?php namespace Dubpub\Publisher;

use Dubpub\Publisher\Abstraction\Contracts\IPublisherHandler;
use Dubpub\Publisher\Abstraction\Abstracts\APublisherHandler;
use Exception;

class PublisherScanner
{
    /**
     * @var string|false
     */
    protected $path;

    /**
     * @var mixed
     */
    protected $composer;

    protected $typeHandlers = [];

    public function getSupportedExtensions()
    {
        return array_keys($this->typeHandlers);
    }

    public function registerTypeHandler($extension, $handlerClassName)
    {
        $this->typeHandlers[$extension] =  $handlerClassName;

        return $this;
    }

    /**
     * @param $type
     * @return IPublisherHandler|APublisherHandler
     */
    protected function makeType($type)
    {
        return (is_string($type = $this->typeHandlers[$type])) ? new $type() : $type;
    }

    /**
     * @param bool $createIfNotExists
     * @param string $createType
     * @return bool|IPublisherHandler
     */
    public function scan($createIfNotExists = true, $createType = 'php')
    {
        $extensions = implode(',', $types = array_keys($this->typeHandlers));
        
        if (!in_array($createType, $types)) {
            throw new \InvalidArgumentException("Unknown handler: {$createType}.");
        }

        $files = glob(realpath($this->path) . "/.publisher.{{$extensions}}", \GLOB_BRACE);

        /**
         * @var IPublisherHandler $handler
         */
        $handler = null;

        if (!$files || count($files) == 0) {
            $handler = $this->makeType($createType);
        } else {
            $createType = pathinfo($files[0], PATHINFO_EXTENSION);
            $handler = $this->makeType($createType);
        }

        $handler->setPath($this->path, $createType);

        if (!$handler->read($createIfNotExists)) {
            return false;
        }

        return $handler;
    }

    public function mergeComposerPackages(IPublisherHandler $handler)
    {
        $extensions = implode(',', array_keys($this->typeHandlers));

        foreach ($files = glob($this->path . '/vendor/**/*') as $packageFolder) {

            $files = glob($packageFolder . '/.publisher.{'.$extensions.'}', GLOB_BRACE);

            if ($files && count($files)) {

                $createType = pathinfo($files[0], PATHINFO_EXTENSION);

                $handlerPackage = $this->makeType($createType);

                $handlerPackage->setPath($packageFolder, $createType);

                if ($handlerPackage->exists()) {
                    $handlerPackage->read();
                    $handler->merge($handlerPackage);
                }
            }
        }
    }

    /**
     * @param string $path
     * @return $this
     * @throws Exception
     */
    public function setPath($path)
    {
        $this->path = realpath($path);

        if (!file_exists($composerPath = $this->path . '/composer.json')) {
            throw new Exception(
                '.package.{' .
                    implode(',', array_keys($this->typeHandlers)) .
                '} should be located same folder as "composer.json".'
            );
        }

        $this->composer = json_decode(file_get_contents($composerPath));

        return $this;
    }
}
