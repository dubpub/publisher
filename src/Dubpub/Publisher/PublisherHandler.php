<?php namespace Dubpub\Publisher;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;

class PublisherHandler
{
    /**
     * @var     string
     */
    protected $vendorPath;

    /**
     * @var     array
     */
    protected $groupList;

    /**
     * @var     array
     */
    protected $packageList;

    /**
     * @var     Models\PublishModel
     */
    private $model;

    /**
     * @var     Abstraction\Contracts\IPublisherHandler
     */
    private $handler;

    /**
     * @var     OutputInterface
     */
    private $output;
    /**
     * @var     \Illuminate\Filesystem\Filesystem
     */
    private $fileSystem;

    public function __construct(
        Models\PublishModel $model,
        Abstraction\Contracts\IPublisherHandler $handler,
        Filesystem $fileSystem
    ) {
        $this->handler = $handler;
        $this->fileSystem = $fileSystem;
        $this->setModel($model);
    }

    /**
     * @param   OutputInterface $output
     * @return  PublisherHandler
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @param   Models\PublishModel $model
     * @return  PublisherHandler
     * @throws  \Exception
     */
    public function setModel(Models\PublishModel $model)
    {
        $this->model = $model;

        // checking if vendor folder is available
        // if not - throwing an exception
        if (!($this->vendorPath = realpath($model->getConfigPath() . '/vendor'))) {
            throw new \Exception("Unable to locate \"vendor\" folder. Try to run \"composer update\"");
        }

        if ($model->getPackageEntry() == '*') {
            $this->packageList = $this->handler->getPackageNames();
        } else {
            // if package name is specified
            // adding it into package list
            $this->packageList = explode(',', $model->getPackageEntry());
        }

        if (($this->groupList = $model->getGroupEntry()) != '*') {
            $this->groupList = explode(',', $model->getGroupEntry());
        }

        $this->groupList = (array) $this->groupList;

        return $this;
    }

    public function process()
    {
        foreach ($this->packageList as $packageName) {
            $this->handlePackage($packageName);
            $this->output->writeln('');
        }
    }

    private function handlePackage($packageName)
    {
        $this->output->writeln("<info>Handling package: </info>{$packageName}");

        /** @var string $packagePath Path to package directory*/
        $packagePath =  $this->vendorPath . DIRECTORY_SEPARATOR . $packageName;


        // skipping if package is not locatable
        if (!$this->handler->packageExists($packageName)) {
            $this->output->writeln(
                "<comment>  [Skipped] Unable to locate \"{$packageName}\" in .publisher file.</comment>"
            );
            return;
        }

        // retrieving group/fileList as groupName => fileList
        /** @var array $groupCollection */
        $groupCollection = $this->handler->getGroupsByPackageName($packageName);

        // if group argument is *, we leave collection as it is,
        // otherwise we take only specified key => value pairs
        if ($this->groupList != ['*']) {
            $groupCollection = array_only($groupCollection, $this->groupList);
            foreach (array_diff($this->groupList, array_keys($groupCollection)) as $ignored) {
                $this->output->writeln(
                    "<comment>  [Skipped] Group \"{$ignored}\" not found in \"{$packageName}\".</comment>"
                );
            }
        }

        // iterating through groups
        foreach ($groupCollection as $groupName => $fileList) {
            $this->handlePackageGroup($packagePath, $groupName, $fileList);
        }
    }

    private function handlePackageGroup($packagePath, $groupName, $fileList)
    {
        $this->output->writeln(
            "<info>  Handling group: </info>{$groupName}"
        );

        // iterating through file list
        foreach ($fileList as $fileString) {
            // removing extra space characters for nice message
            $fileString = preg_replace('/(\ {1,})/is', '', $fileString);

            $this->output->writeln("<info>  Handling item:\n  -  </info><comment>" . $fileString . "</comment>");

            list($sourcesPath, $targetPath) = $this->extractNotation($fileString);

            // creating targetPath if it does not exist
            if (!$this->fileSystem->isDirectory($targetPath)) {
                $this->fileSystem->makeDirectory($targetPath, 0777, true, true);
            }

            // if sourcesPath contains link syntax
            if (substr_count($sourcesPath, '@') == 0) {
                // merging package path and sourcePath
                $sourcesPath = $packagePath . '/' . trim($sourcesPath, '\\/');

                // getting file/files/folder by sourcePath mask
                foreach (glob($sourcesPath) as $source) {
                    $this->output->writeln(
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
                    $currentSourceTarget = $targetPath . '/' . basename($source) ;

                    if (is_link($currentSourceTarget)) {
                        $this->output->writeln(
                            '     Removing old symlynk at '. $currentSourceTarget
                        );
                        unlink($currentSourceTarget);
                    }

                    $this->output->writeln(
                        "     Linking:\n\t" .
                        "<info>source:</info> [<comment>{$source}</comment>]" .
                        "\n\t" .
                        "<info>target:</info> [<comment>{$targetPath}</comment>]"
                    );

                    symlink($source, $currentSourceTarget);
                }
            }

            $this->output->writeln("");
        }
    }

    private function extractNotation($fileString)
    {
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
            $fileStringNotation[1] = $this->model->getPublishPath() . '/' . $fileStringNotation[1];

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
        } elseif (count($fileStringNotation) == 1) {
            // if file notation string does not
            // contain destination symbol
            if ($fileStringNotation[0][0] == '/') {
                throw new \LogicException('.publisher does not work with absolute paths');
            }

            $fileStringNotation[1] = $this->model->getPublishPath();
        } else {
            // if we meet nonsense typo
            throw new \LogicException("Unexpected syntax in:" . $fileString);
        }

        return $fileStringNotation;
    }
}
