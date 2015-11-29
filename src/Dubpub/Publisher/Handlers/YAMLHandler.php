<?php namespace Dubpub\Publisher\Handlers;

use Dubpub\Publisher\Abstraction\Abstracts\APublisherHandler;
use Dubpub\Publisher\Abstraction\Contracts\IPublisherHandler;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class YAMLHandler extends APublisherHandler implements IPublisherHandler
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Dumper
     */
    private $dumper;

    public function __construct(Parser $parser, Dumper $dumper)
    {
        $this->parser = $parser;
        $this->dumper = $dumper;
    }

    /**
     * @param $filePath
     * @param array $data
     * @return bool
     */
    public function writeData($filePath, array $data)
    {
        return file_put_contents($filePath, $this->dumper->dump($data, 4)) !== false;
    }

    /**
     * @param $filePath
     * @return array
     */
    public function readData($filePath)
    {
        return $this->parser->parse(file_get_contents($filePath));
    }
}
