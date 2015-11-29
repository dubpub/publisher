<?php namespace Dubpub\Publisher\Commands;

use Dubpub\Publisher\Abstraction\Contracts\IPublisherHandler;
use Dubpub\Publisher\PublisherScanner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    /**
     * @var PublisherScanner
     */
    private $publisherScanner;

    public function __construct($name = null, PublisherScanner $publisherScanner = null)
    {
        $this->publisherScanner = $publisherScanner;
        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $extensions = $this->publisherScanner->getSupportedExtensions();

        $this->addArgument(
            'publisherType',
            InputArgument::OPTIONAL,
            'one of supported types: ' . implode(',', $extensions),
            'php'
        );

        $this->addArgument(
            'initPath',
            InputArgument::OPTIONAL,
            'Init path. Default path is launch path. ',
            getcwd()
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('initPath');

        $type = $input->getArgument('publisherType');

        /**
         * @var IPublisherHandler $handler
         */
        $handler = $this->publisherScanner->setPath($path)->scan(true, $type);

        $this->publisherScanner->mergeComposerPackages($handler);

        $handler->write();
    }
}
