<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class IOResponse
 */
class IOResponse implements JsonSerializable
{
    /**
     * @var array
     */
    private $assigns;

    /**
     * @var array
     */
    private $scripts;

    /**
     *
     */
    public function __constructor()
    {
        $this->assigns = array();
        $this->scripts = array();
    }

    /**
     * @param $target
     * @param $attr
     * @param $data
     */
    public function assign($target, $attr, $data)
    {
        $this->assigns[] = (object) array(
            'target' => $target,
            'attr'   => $attr,
            'data'   => $data
        );
    }

    /**
     * @param string $js
     */
    public function script($js)
    {
        $this->scripts[] = $js;
    }

    /**
     * @param $function
     */
    public function jsfunc($function)
    {
        $arguments = func_get_args();
        array_shift($arguments);

        $filtered = $arguments;

        array_walk($filtered, function (&$value, $key) {

            switch (gettype($value)) {
                case 'string':
                    $value = json_encode($value);
                    break;

                case 'boolean':
                    $value = $value ? 'true' : 'false';
                    break;

                case 'integer':
                case 'double':
                    // nothing todo
                    break;

                case 'array':
                case 'object':
                    $value = json_encode($value);
                    break;

                case 'resource':
                case 'NULL':
                case 'unknown type':
                default:
                    $value = 'null';
                    break;
            }
        });

        $argumentlist = implode(', ', $filtered);
        $syntax       = sprintf('%s(%s);', $function, $argumentlist);

        //$this->script("console.warn('%c CALL %c {$syntax}', 'background: #e86c00; color: #fff;', 'background: transparent; color: #000; font-weight: normal;');");

        $this->script($syntax);

        /*
        $this->script("console.groupCollapsed('%c CALL %c {$function}()', 'background: #e86c00; color: #fff;', 'background: transparent; color: #000; font-weight: normal;');");
        $this->script("console.log('%c METHOD %c {$function}()', 'background: #e8e8e8; color: #333;', 'background: transparent; color: #000; font-weight: normal;');");        
        $this->script("console.log('%c PARAMS ', 'background: #e8e8e8; color: #333;', ".json_encode($arguments).");");
        $this->script("console.groupEnd();");
        */
    }

    /**
     * @return string
     */
    public function generateCallTrace()
    {
        $str = (new Exception())
            ->getTraceAsString();
        $trace = explode("\n", $str);
        $trace = array_reverse($trace);
        array_shift($trace);
        array_pop($trace);
        $result = array();

        foreach ($trace as $i => $t) {
            $result[] = '#' . ($i + 1) . substr($t, strpos($t, ' '));
        }

        return implode("\n", $result);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'js'  => $this->scripts,
            'css' => $this->assigns,
        );
    }
}
