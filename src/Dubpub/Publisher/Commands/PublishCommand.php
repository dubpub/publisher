<?php namespace Dubpub\Publisher\Commands;

use Dubpub\Publisher\Abstraction\Contracts\IPublisherHandler;
use Dubpub\Publisher\Filters\PublishFilter;
use Dubpub\Publisher\Models\PublishModel;
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

    public function __construct($name = null, PublisherScanner $publisherScanner)
    {
        $this->publisherScanner = $publisherScanner;
        $this->fileSystem = new Filesystem();
        parent::__construct($name);
    }


    protected function interact(InputInterface $input, OutputInterface $output)
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

        // initializing empty package list
        $packageList = [];

        // checking we need to publish
        // every package entry
        if ($model->getPackageEntry() == '*') {
            $packageList = $handler->getPackageNames();
        } else {
            // if package name is specified
            // adding it into package list
            $packageList = explode(',', $model->getPackageEntry());
        }

        if (($groupList = $model->getGroupEntry()) != '*') {
            $groupList = explode(',', $model->getGroupEntry());
        }

        $groupList = (array) $groupList;
        
        // checking if vendor folder is available
        // if not - throwing an exception
        if (!($vendorPath = realpath($model->getConfigPath() . '/vendor'))) {
            throw new \Exception(
                "Unable to locate \"vendor\" folder. Try to run \"composer update\""
            );
        }

        // iterating through packages
        foreach ($packageList as $packageName) {
            $output->writeln("<info>Handling package: </info>{$packageName}");

            /** @var string $packagePath Path to package directory*/
            $packagePath =  $vendorPath . DIRECTORY_SEPARATOR . $packageName;

            // skipping if package is not locatable
            if (!$handler->packageExists($packageName)) {
                $output->writeln(
                    "<comment>  [Skipped] Unable to locate \"{$packageName}\" in .publisher file.</comment>"
                );
                continue;
            }

            // retrieving group/fileList as groupName => fileList
            $groupCollection = $handler->getGroupsByPackageName($packageName);

            // if group argument is *, we leave collection as it is,
            // otherwise we take only specified key => value pairs

            if ($groupList != ['*']) {
                $groupCollection = array_only($groupCollection, $groupList);
                foreach (array_diff($groupList, array_keys($groupCollection)) as $ignored) {
                    $output->writeln(
                        "<comment>  [Skipped] Group \"{$ignored}\" not found in \"{$packageName}\".</comment>"
                    );
                }
            }


            // iterating through groups
            foreach ($groupCollection as $groupName => $fileList) {
                $output->writeln(
                    "<info>  Handling group: </info>{$groupName}"
                );

                // iterating through file list
                foreach ($fileList as $fileString) {
                    // removing extra space characters for nice message
                    $fileString = preg_replace('/(\ {1,})/is', '', $fileString);

                    $output->writeln("<info>  Handling item:\n  -  </info><comment>" . $fileString . "</comment>");

                    // removing extra space characters for further work
                    $fileString = preg_replace('/(\ {0,1})/is', '', $fileString);

                    // extracting file string notation
                    $fileStringNotation = explode('->', $fileString);

                    // if file notation contains copy/link
                    // via destination "->" symbol
                    if (count($fileStringNotation) == 2) {
                        // throwing an exception if target path is absolute

                        if ($fileStringNotation[0][0] == '/') {
                            throw new \LogicException('.publisher does not work with absolute paths');
                        }

                        if ($fileStringNotation[1][0] == '/') {
                            throw new \LogicException('.publisher does not work with absolute paths');
                        }

                        // composing source notation - (publish path + directory separator + distination)
                        $fileStringNotation[1] = $model->getPublishPath() . '/' . $fileStringNotation[1];

                        // checking if source want to be published
                        // in one of possible locations
                        // e.g.: "{public,web}/assets" will check if
                        // public/assets or public/web is available.
                        // e.g.: "assets/{scripts,js}" will check if
                        // assets/scripts or assets/js is available.
                        $reg = '/(.*?)(\{)([\w\d\.\-\@\:\,]{1,})(\})([\w\d\/\.\-\@\:\,]{0,})/i';

                        if (preg_match($reg, $fileStringNotation[1], $matches)) {
                            // converting variants into array
                            // e.g. {public,web,htdocs}/assets will produce ['public', 'web', 'htdocs']
                            $variants = explode(',', $matches[3]);

                            // suppose that none of variants exists
                            $existed = false;

                            foreach ($variants as $publishVariant) {
                                // if path exists setting it as
                                if ($existed = realpath($matches[1] . $publishVariant)) {
                                    // replacing source notation with target path
                                    // and breaking the loop
                                    $fileStringNotation[1] = $matches[1] . $publishVariant . $matches[5];
                                    break;
                                }
                            }

                            if (!$existed) {
                                // if none of variants exists
                                // replacing source notation with first "posible"
                                // target path
                                $fileStringNotation[1] = $matches[1] . $variants[0] . $matches[5];
                            }
                        }

                    } elseif(count($fileStringNotation) == 1) {
                        // if file notation string does not
                        // contain destination symbol

                        if ($fileStringNotation[0][0] == '/') {
                            throw new \LogicException('.publisher does not work with absolute paths');
                        }

                        $fileStringNotation[1] = $model->getPublishPath();
                    } else {
                        // if we meet nonsense typo
                        throw new \LogicException("Unexpected syntax in:" . $fileString);
                    }


                    list($sourcesPath, $targetPath) = $fileStringNotation;

                    // creating targetPath if it does not exist
                    if (! $this->fileSystem->isDirectory($targetPath)) {
                        $this->fileSystem->makeDirectory($targetPath, 0777, true, true);
                    }

                    // if sourcesPath contains link syntax
                    if (substr_count($sourcesPath, '@') == 0) {
                        // merging package path and sourcePath
                        $sourcesPath = $packagePath . '/' . trim($sourcesPath, '\\/');

                        // getting file/files/folder by sourcePath mask
                        foreach (glob($sourcesPath) as $source) {
                            $output->writeln(
                                "     Copying:\n\t" .
                                "<info>source:</info> [<comment>{$source}</comment>]" .
                                "\n\t" .
                                "<info>target:</info> [<comment>{$targetPath}</comment>]"
                            );

                            // if source is directory
                            if (is_dir($source)) {
                                $this->fileSystem->copyDirectory($source, $targetPath);
                            } else {
                                $this->fileSystem->copy($source, $targetPath . '/' . basename($source));
                            }

                        }
                    } else {
                        // composing sourcePath and replacing @ character
                        $sourcesPath = $packagePath . '/' . str_replace('@', '', trim($sourcesPath, '\\/'));

                        // running sourcePath as mask
                        foreach (glob($sourcesPath) as $source) {
                            // link target path
                            $_currentTarget = $targetPath . '/' . basename($source) ;

                            if (is_link($_currentTarget)) {
                                $output->writeln(
                                    '     Removing old symlynk at '. $_currentTarget
                                );
                                unlink($_currentTarget);
                            }
                            $output->writeln(
                                "     Linking:\n\t" .
                                "<info>source:</info> [<comment>{$source}</comment>]" .
                                "\n\t" .
                                "<info>target:</info> [<comment>{$targetPath}</comment>]"
                            );

                            symlink($source, $_currentTarget);
                        }
                    }

                    $output->writeln("");
                }
            }

            $output->writeln('');
        }
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