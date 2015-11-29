<?php namespace Dubpub\Publisher\Handlers;

use Dubpub\Publisher\Abstraction\Abstracts\APublisherHandler;
use Dubpub\Publisher\Abstraction\Contracts\IPublisherHandler;

class PHPHandler extends APublisherHandler implements IPublisherHandler
{
    /**
     * @param $filePath
     * @return array
     */
    public function readData($filePath)
    {
        return include $filePath;
    }

    /**
     * @param $filePath
     * @param array $data
     * @return bool
     */
    public function writeData($filePath, array $data)
    {
        return file_put_contents($filePath, '<?php return ' . $this->var_export_braces($data) . ';');
    }

    public function var_export_braces($var, $indent = null)
    {
        if ($indent === null) {
            $indent = "";
        }

        $result = '';

        switch (gettype($var)) {
            case "string":
                $result .= '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
                break;
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = $indent . "    "
                        . ($indexed ? "" : $this->var_export_braces($key) . " => ")
                        . $this->var_export_braces($value, $indent . "    ");
                }

                $result .= "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
                break;
        }

        return $result;
    }
}