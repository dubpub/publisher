<?php
namespace Dubpub\Publisher\Models;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Created by PhpStorm.
 * User: madman
 * Date: 09.11.15
 * Time: 0:33
 */
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

    protected $groups = '*';

    /**
     * @var InputInterface
     */
    private $input;

    public function __construct(InputInterface $input)
    {
        $this->setInput($input);
    }

    public function validate()
    {
        if (!$this->configPath) {
            throw new \InvalidArgumentException('Unable to locate .publisher path.');
        }

        if (!$this->publishPath) {
            throw new \InvalidArgumentException('Unable to locate publish path.');
        }

        if ($this->packageEntry != '*') {
            if (preg_match('/([\w\d\_\-\.]{1,})(\/)([\w\d\_\-\.]{1,})/', $this->packageEntry) === 0) {
                throw new \InvalidArgumentException('Invalid package format: "*" or "vendor/package".');
            }
        }

        $this->groups = ['*'];

        return true;
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

        $this->configPath = realpath($input->getOption('configPath'));
        $this->publishPath = realpath($input->getArgument('publishPath'));
        $this->packageEntry = $input->getArgument('package');
        //$this->groups = $input->getArgument('package');

        $this->validate();

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
}
