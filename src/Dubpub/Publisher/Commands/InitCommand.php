<?php namespace Dubpub\Publisher\Commands;

use Dubpub\Publisher\PublisherScanner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Dubpub\Publisher\Contracts\IConfigGroup;
use Dubpub\Publisher\Contracts\IConfigReader;
use Dubpub\Publisher\Readers\PHP\PHPReader;
use Dubpub\Publisher\Readers\Yaml\YAMLReader;

class PublishCommand extends Command
{
    /**
     * Config file
     * @var string
     */
    protected $configFile;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    protected $supportedTypes = [
        'php' => PHPReader::class,
        'yml' => YAMLReader::class,
        'yaml' => YAMLReader::class
    ];

    protected function configure()
    {
        $this->addArgument('package', InputArgument::OPTIONAL, 'Vendor package', '*');
        $this->addArgument('publish_group', InputArgument::OPTIONAL, 'Group to publish', '*');
        $this->addArgument('publish_path', InputArgument::OPTIONAL, 'Path where to publish', getcwd());

        $this->addOption('configPath', 'c', InputOption::VALUE_OPTIONAL, 'Configure file', getcwd());

        $this->setDescription(
            "Publishes assets, provided in .publisher.".implode('|', array_keys($this->supportedTypes))
        );

        $this->setName('publish');

        $this->fileSystem = new Filesystem();
    }

    /**
     * @param $configFileDirectory
     * @param OutputInterface $output
     * @return PHPReader
     */
    protected function loadConfigReader($configFileDirectory, OutputInterface $output)
    {
        foreach ($this->supportedTypes as $type => $reader) {
            if (file_exists($configFilePath = realpath($configFileDirectory . '/.publisher.' . $type))) {
                $configReader = new $this->supportedTypes[$type]();
                $configReader->setPath($configFilePath);

                $output->writeln("<info>Reading config from: {$configFilePath}</info>");

                $configReader->read();
                return $configReader;
            }
        }

        $fileNames = [];

        foreach (array_keys($this->supportedTypes) as $type) {
            $fileNames[] = '.publisher.'.$type;
        }

        throw new \InvalidArgumentException(
            'Unable to locate any config file, try to create one of those: ' . implode(', ', $fileNames)
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFileDirectory = realpath($input->getOption('configPath'));

        if (!$configFileDirectory) {
            throw new \InvalidArgumentException(
                'Unable to locate config provided config file directory'
            );
        }

        /**
         * @var IConfigReader
         */
        $configReader = $this->loadConfigReader($configFileDirectory, $output);

        $launchPath = getcwd();

        $publishPath = $input->getArgument('publish_path');

        if (!(realpath($publishPath))) {
            $publishPath = $publishPath[0] == '/' ? $publishPath :  $launchPath . '/' . ltrim($publishPath, './\\');

            $this->fileSystem->mkdir($publishPath, 0777);

            $publishPath = realpath($publishPath);

            $output->writeln("<info>Created: {$publishPath}</info>");
        } else {
            $publishPath = realpath($publishPath[0] == '/' ? $publishPath :  $launchPath . '/' . ltrim($publishPath, './\\'));;
        }

        $output->writeln("<info>Writing to path: {$publishPath}</info>" . \PHP_EOL);

        $publishScanner = new PublisherScanner();

        $publishScanner->registerTypeHandlers($this->supportedTypes);

        $vendorPackage = $input->getArgument('package');

        if (!file_exists($launchPath . '/vendor')) {
            die('fuck you');
        }

        if (!preg_match('/(([\w\d\_\-]{1,})(\/)([\w\d\_\-]{1,})||(\*))/', $vendorPackage)) {
            die('wrong package name');
        }




        die;

        $groups = ($groupName = $input->getArgument('publish_group')) == '*' ?
            $configReader->getGroups() :
            $configReader->getGroupByNames([$groupName]);


        /** @var IConfigGroup[] $groups */
        foreach($groups as $group) {
            $output->writeln("<info>Handling group \"{$group->getName()}\"</info>");

            foreach ($group->getPaths() as $path) {
                $output->writeln('Handling path: ' . $path);

                if (is_dir($sourceFile = realpath($_path = $path[0] == '/' ? $path : (getcwd() . '/' . $path)))) {

                    $output->writeln('Copying target directory: ' . $sourceFile);

                    $directoryIterator = new \RecursiveDirectoryIterator($sourceFile, \RecursiveDirectoryIterator::SKIP_DOTS);
                    $iterator = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::SELF_FIRST);

                    $_publishPath = $publishPath . '/' . basename($sourceFile);

                    foreach ($iterator as $item) {
                        if ($item->isDir()) {
                            $this->fileSystem->mkdir($sourceFile . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                        } else {
                            $this->fileSystem->copy($item, $_publishPath . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                        }

                        $output->writeln('Copying target: ' . $item . 'to' . $_publishPath);
                    }


                    //
                } elseif (file_exists($sourceFile)) {
                    $path = realpath($path[0] == '/' ? $path : getcwd() . '/' . $path);
                    if ($path) {
                        $output->writeln('Copying target file: ' . $path . 'to' . $publishPath);
                        $this->fileSystem->copy($path, $publishPath . '/' . basename($path), true);
                    } else {
                        $output->writeln('Failed: ' . $path);
                    }
                } else {
                    var_dump($sourceFile, $_path) || die;
                }
            }
        }

    }
}