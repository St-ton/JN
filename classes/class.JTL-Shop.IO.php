<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class IO
 */
class IO
{
    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @var array
     */
    private $functions = [];

    /**
     * IO constructor.
     */
    private function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        return self::$instance === null ? new self() : self::$instance;
    }

    /**
     * Registers a PHP function or method.
     * This makes the function available for XMLHTTPRequest requests.
     *
     * @param string        $name
     * @param null|callable $function
     * @return $this
     * @throws Exception
     */
    public function register($name, $function = null)
    {
        if ($this->exists($name)) {
            throw new Exception("Function already registered");
        }

        if ($function === null) {
            $function = $name;
        }

        $this->functions[$name] = $function;

        return $this;
    }

    /**
     * @param string $reqString
     * @return mixed
     * @throws Exception
     */
    public function handleRequest($reqString)
    {
        $request = json_decode($reqString, true);

        if (($errno = json_last_error()) != JSON_ERROR_NONE) {
            throw new Exception("Error {$errno} while decoding data");
        }

        if (!isset($request['name'], $request['params'])) {
            throw new Exception("Missing request property");
        }

        return $this->execute($request['name'], $request['params']);
    }

    public function respondAndExit($data)
    {

    }

    /**
     * Check if function exists
     *
     * @param string $name
     * @return bool
     */
    public function exists($name)
    {
        return isset($this->functions[$name]);
    }

    /**
     * Executes a registered function
     *
     * @param string $name
     * @param mixed  $params
     * @return mixed
     * @throws Exception
     */
    public function execute($name, $params)
    {
        if (!$this->exists($name)) {
            throw new Exception("Function not registered");
        }

        $function = $this->functions[$name];

        if (is_array($function)) {
            $ref = new ReflectionMethod($function[0], $function[1]);
        } else {
            $ref = new ReflectionFunction($function);
        }

        if ($ref->getNumberOfRequiredParameters() > count($params)) {
            throw new Exception("Wrong required parameter count");
        }

        return call_user_func_array($function, $params);
    }
}
