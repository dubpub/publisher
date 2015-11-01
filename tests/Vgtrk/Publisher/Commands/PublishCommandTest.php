<?php namespace Vgtrk\Publisher\Commands;

use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PublishCommand
     */
    protected $testInstance;

    /**
     * @var InputInterface|Mock
     */
    protected $input;

    /**
     * @var OutputInterface|Mock
     */
    protected $output;

    private function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    private function clearUp()
    {
        if ($name = realpath(getcwd() . '/./' . 'tests/tests/expect')) {
            $this->delTree($name);
        }
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->input = $this->getMock(
            InputInterface::class
        );

        $this->output = $this->getMock(
            OutputInterface::class
        );



        $this->output->expects($this->any())->method('writeln')
            ->will($this->returnCallback(function ($message) {
                //echo $message . \PHP_EOL;
            }));

        $this->testInstance = new PublishCommand();

        $this->clearUp();
    }

    private function makeGroupTest($type)
    {
        $launch = 0;

        $this->input->expects($this->any())->method('getArgument')
            ->will($this->returnCallback(function ($argument) use (&$launch, $type) {

                switch ($argument) {
                    case 'publish_path':
                        switch ($launch) {
                            case 0:
                                return 'tests/tests/expect/'.$type;
                            case 1:
                                return 'tests/tests/expect/folder';
                            case 2:
                                return 'tests/tests/expect/folder';
                        }
                        break;
                    case 'publish_group':
                        switch ($launch) {
                            case 0:
                                return '*';
                            case 1:
                                return 'folder_file';
                            case 2:
                                return 'unknown';
                        }
                        break;
                }
            }));

        $this->input->expects($this->any())->method('getOption')
            ->will($this->returnCallback(function ($option) use (&$launch, $type) {
                switch ($option) {
                    case 'configPath':
                        return 'tests/tests/'.$type;
                }
            }));

        $this->testInstance->run($this->input, $this->output);

        $this->assertTrue(file_exists(realpath('tests/tests/expect/'.$type.'/folderA/folderB/test.css')));

        $this->assertTrue(file_exists(realpath('tests/tests/expect/'.$type.'/testTest.css')));

        $launch += 1;

        $this->testInstance->run($this->input, $this->output);

        $this->assertTrue(file_exists(realpath('tests/tests/expect/folder/folderB/test.css')));

        $this->assertTrue(file_exists(realpath('tests/tests/expect/folder/testTest.css')));

        $launch += 1;

        try {
            $this->testInstance->run($this->input, $this->output);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }
    }

    private function makeConfigTest($type)
    {
        $launch = 0;

        $this->input->expects($this->any())->method('getArgument')
            ->will($this->returnCallback(function ($argument) use (&$launch, $type) {
                switch ($argument) {
                    case 'publish_path':
                        return 'tests/tests/expect/'.$type;
                    case 'publish_group':
                        return 'folder_file';
                }
            }));

        $this->input->expects($this->any())->method('getOption')
            ->will($this->returnCallback(function ($option) use (&$launch, $type) {
                switch ($option) {
                    case 'configPath':
                        switch ($launch) {
                            case 0:
                                return 'tests/tests/'.$type.'_bad_folder';
                            case 1:
                                return 'tests/tests/'.$type.'_bad';
                            case 2:
                                return 'tests/tests/'.$type;
                        }
                }
            }));

        try {
            $this->testInstance->run($this->input, $this->output);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }

        $launch++;

        try {
            $this->testInstance->run($this->input, $this->output);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }

        $launch++;

        $this->testInstance->run($this->input, $this->output);

        $this->assertTrue(file_exists('tests/tests/expect/'.$type.'/testTest.css'));
        $this->assertTrue(file_exists('tests/tests/expect/'.$type.'/folderB'));
    }

    public function testPhpReaderGroups()
    {
        $this->makeGroupTest('php');
    }

    public function testYmlReaderGroups()
    {
        $this->makeGroupTest('yml');
    }

    public function testPhpReaderConfig()
    {
        $this->makeConfigTest('php');
    }

    public function testYmlReaderConfig()
    {
        $this->makeConfigTest('yml');
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->clearUp();
        parent::tearDown(); // TODO: Change the autogenerated stub
    }
}
