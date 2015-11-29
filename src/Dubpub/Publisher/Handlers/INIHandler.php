<?php namespace Dubpub\Publisher\Handlers;

use Dubpub\Publisher\Abstraction\Abstracts\APublisherHandler;
use Dubpub\Publisher\Abstraction\Contracts\IPublisherHandler;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;

class INIHandler extends APublisherHandler implements IPublisherHandler
{
    /**
     * @param $filePath
     * @param array $data
     * @return bool
     */
    public function writeData($filePath, array $data)
    {
        return file_put_contents($filePath, $this->valueToIni($data)) !== false;
    }

    protected function valueToIni(array $data)
    {
        $result = "";

        foreach ($data as $section => $values) {
            $result .= "[{$section}]\n";

            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    for ($i = 0; $i < count($value); $i++) {
                        $result .= "{$key}[] = \"{$value[$i]}\"\n";
                    }
                }
            }

            $result .= "\n";
        }

        return $result;
    }

    /**
     * @param $filePath
     * @return array
     */
    public function readData($filePath)
    {
        return parse_ini_file($filePath, true, INI_SCANNER_RAW);
    }
}
