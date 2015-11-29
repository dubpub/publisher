<?php namespace Dubpub\Publisher\Handlers;

use Dubpub\Publisher\Abstraction\Abstracts\APublisherHandler;
use Dubpub\Publisher\Abstraction\Contracts\IPublisherHandler;

class JSONHandler extends APublisherHandler implements IPublisherHandler
{
    /**
     * @param $filePath
     * @return array
     */
    public function readData($filePath)
    {
        return json_decode(file_get_contents($filePath), true);
    }

    /**
     * @param $filePath
     * @param array $data
     * @return bool
     */
    public function writeData($filePath, array $data)
    {
        return file_put_contents($filePath, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) !== false;
    }
}