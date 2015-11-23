<?php namespace Dubpub\Publisher\Filters;

use Symfony\Component\Console\Input\InputInterface;

class PublishFilter
{
    /**
     * @var InputInterface
     */
    private $input;

    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * @param string $paramName
     * @param string $regExpAny
     * @param string $regExpOne
     */
    private function checkParam($paramName, $regExpAny, $regExpOne)
    {
        $value = $this->input->getArgument($paramName);

        if ($value != '*') {
            // removing extra space characters if argument is passed
            // in quotes
            $value = preg_replace('/(\ {1,}|\*{1,})/', '', $value);

            // checking if it's at least one entry
            if (!preg_match($regExpAny, $value)) {
                throw new \InvalidArgumentException('Invalid ' . $paramName . ' format: ' . $value);
            }

            // imploding entries names
            $entryNames = explode(',', $value);

            foreach ($entryNames as $entryName) {
                if (!preg_match($regExpOne, $entryName)) {
                    throw new \InvalidArgumentException('Invalid '. $paramName . ' format: ' . $entryName);
                }
            }

            // updating entry value
            $this->input->setArgument($paramName, $value);
        }
    }

    private function checkPath($optionName, $message)
    {
        $path = realpath($this->input->getOption($optionName));

        if (!$path) {
            throw new \InvalidArgumentException($message);
        }

        $this->input->setOption($optionName, $path);
    }

    private function processOptions()
    {
        $this->checkPath('configPath', 'Unable to locate .publisher path.');
        $this->checkPath('publishPath', 'Unable to locate publish path.');
    }

    private function processPackageParam()
    {
        // checks if value contains at least one package name
        $regExpAny = '/([\_\-\.\d\w]{1,})(\/)([\-\.\d\w]{1,})/is';
        // checks if value contains only one package name
        $regExpOne = '/([\_\-\.\d\w]{1,})(\/)([\-\.\d\w]{1,})$/is';

        $this->checkParam('package', $regExpAny, $regExpOne);
    }

    private function processGroupParam()
    {
        // checks if value contains at least one group name
        $regExpAny = '/([\_\-\.\d\w\ \*]{1,})/is';

        $this->checkParam('group', $regExpAny, $regExpAny);
    }



    public function process()
    {
        $this->processPackageParam();
        $this->processGroupParam();

        $this->processOptions();
    }
}