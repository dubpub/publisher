<?php namespace Dubpub\Publisher\Commands;

use Dubpub\Publisher\Abstraction\Contracts\IPublisherHandler;
use Dubpub\Publisher\Filters\PublishFilter;
use Dubpub\Publisher\Models\PublishModel;
use Dubpub\Publisher\PublisherHandler;
use Dubpub\Publisher\PublisherScanner;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PublishCommand extends Command
{
    const ARG_PACKAGE_DESC = "Package to publish. All by default.";

    const ARG_GROUP_DESC =  "Group to publish. All by default.";

    const ARG_PUBLISH_PATH_DESC = "Path where to publish. Default path is launch path.";

    const OPT_OPTIONS_PATH = "Config path. Default path is launch path.";

    /**
     * @var PublisherScanner
     */
    private $publisherScanner;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    public function __construct($name = null, PublisherScanner $publisherScanner = null)
    {
        $this->publisherScanner = $publisherScanner;
        $this->fileSystem = new Filesystem();
        parent::__construct($name);
    }


    protected function interact(InputInterface $input)
    {
        $filter = new PublishFilter($input);

        // preparing package and group params:
        // validating and parsing
        $filter->process();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $model = new PublishModel($input);

        // setting path to scan for .publisher.* config file
        $this->publisherScanner->setPath($model->getConfigPath());

        /**
         * @var IPublisherHandler $handler
         */
        $handler = $this->publisherScanner->scan(false);

        // if handler is null, throwing an exception
        // with message
        if (!$handler) {
            // retrieving available extensions
            // and making mask-like string
            $extensions = implode('|', $this->publisherScanner->getSupportedExtensions());
            throw new \Exception('You have to create .publisher.(' . $extensions. ') file first.');
        }

        $publisherHandler = new PublisherHandler($model, $handler, $this->fileSystem);

        $publisherHandler->setOutput($output)->process();
    }

    protected function configure()
    {
        $this->addArgument('package', InputArgument::OPTIONAL, self::ARG_PACKAGE_DESC, '*');

        $this->addArgument('group', InputArgument::OPTIONAL, self::ARG_GROUP_DESC, '*');

        $this->addOption('publishPath', 'p', InputOption::VALUE_OPTIONAL, self::ARG_PUBLISH_PATH_DESC, getcwd());

        $this->addOption('configPath', 'c', InputOption::VALUE_OPTIONAL, self::OPT_OPTIONS_PATH, getcwd());

        $description = sprintf(
            "Publishes assets, provided in .publisher.{%s}",
            implode(',', $this->publisherScanner->getSupportedExtensions())
        );

        $this->setDescription($description);
    }
}
