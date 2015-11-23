<?php namespace Dubpub\Publisher\Models;

use Symfony\Component\Console\Input\InputInterface;

class PublishModel
{
    /**
     * @var string
     */
    protected $configPath;
    /**
     * @var string
     */
    protected $publishPath;
    /**
     * @var string
     */
    protected $packageEntry;

    protected $group = '*';

    /**
     * @var InputInterface
     */
    private $input;

    public function __construct(InputInterface $input)
    {
        $this->setInput($input);
    }

    /**
     * @return string
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * @param InputInterface $input
     * @return PublishModel
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;

        $this->configPath = $input->getOption('configPath');
        $this->publishPath = $input->getOption('publishPath');

        $this->packageEntry = $input->getArgument('package');
        $this->group = $input->getArgument('group');

        return $this;
    }

    /**
     * @return string
     */
    public function getPublishPath()
    {
        return $this->publishPath;
    }

    /**
     * @return string
     */
    public function getPackageEntry()
    {
        return $this->packageEntry;
    }

    public function getGroupEntry()
    {
        return $this->group;
    }
}
