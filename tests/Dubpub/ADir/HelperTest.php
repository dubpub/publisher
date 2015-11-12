<?php

use Dubpub\Publisher\PublisherScanner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PHPUnit_Framework_MockObject_MockObject as Mock;

define('TEST_PATH', realpath(__DIR__ . '/../../tests'));


abstract class HelperTest extends \PHPUnit_Framework_TestCase
{
    protected $expectation = [
        "demo-publisher/demo-package" => [
        ],
        "dubpub/publisher" => [
            "assets" => [
                "assets/scripts       ->  {public,web,htdocs}/assets/js/",
                "assets/styles        ->  {public,web,htdocs}/assets/css/"
            ],
            "configs" => [
                "configs/*.ini.dist -> configs/dist"
            ],
            "link" => [
                "@link/* -> link"
            ]
        ]
    ];

    /**
     * @var Command
     */
    protected $testInstance;
    /**
     * @var InputInterface|Mock
     */
    protected $inputMock;
    /**
     * @var OutputInterface|Mock
     */
    protected $outputMock;
    /**
     * @var PublisherScanner
     */
    protected $publisherScanner;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->prepareFolder();

        $this->inputMock = $this->getMock(InputInterface::class);
        $this->outputMock = $this->getMock(OutputInterface::class);

        $this->publisherScanner = new PublisherScanner();

        $this->publisherScanner->registerTypeHandler('php', \Dubpub\Publisher\Handlers\PHPHandler::class);
        $this->publisherScanner->registerTypeHandler('json', \Dubpub\Publisher\Handlers\JSONHandler::class);

        $yamlHandler = new \Dubpub\Publisher\Handlers\YAMLHandler(
            new \Symfony\Component\Yaml\Parser(),
            new \Symfony\Component\Yaml\Dumper()
        );

        $this->publisherScanner->registerTypeHandler('yml', $yamlHandler);
        $this->publisherScanner->registerTypeHandler('yaml', $yamlHandler);

        $this->testInstance = $this->getCommand($this->publisherScanner);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();

        unset($this->testInstance);
        unset($this->publisherScanner);
        unset($this->inputMock);
        unset($this->outputMock);

        $this->prepareFolder();
    }

    /**
     *
     * @param PublisherScanner $publisherScanner
     * @return Command
     */
    abstract public function getCommand(PublisherScanner $publisherScanner);

    public function prepareFolder()
    {
        $filesystem = new \Illuminate\Filesystem\Filesystem();

        foreach ($filesystem->glob(TEST_PATH . DIRECTORY_SEPARATOR . '{,.}*', GLOB_BRACE) as $file) {

            switch (basename($file)) {
                case 'vendor':
                case 'composer.json':
                case '.':
                case '..':
                    continue;
                default:
                    if (is_dir($file)) {
                        $filesystem->deleteDirectory($file);
                    } else {
                        $filesystem->delete($file);
                    }
            }
        }

    }
}