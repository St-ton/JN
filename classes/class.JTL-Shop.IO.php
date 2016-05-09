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
     * @var array
     */
    private static $functions = array();

    /**
     * Registers a PHP function or method.
     * This makes the function available for XMLHTTPRequest requests.
     *
     * @param string      $name
     * @param null|string $function
     * @return $this
     * @throws Exception
     */
    public function register($name, $function = null)
    {
        if ($this->exists($name)) {
            throw new Exception("Function already registered");
        }

        if (is_null($function)) {
            $function = $name;
        }

        self::$functions[$name] = $function;

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

        if (!(isset($request['name']) && isset($request['params']))) {
            throw new Exception("Missing request property");
        }

        return $this->execute($request['name'], $request['params']);
    }

    /**
     * Check if function exists
     *
     * @param string $name
     * @return bool
     */
    public function exists($name)
    {
        return isset(self::$functions[$name]);
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

        $function = self::$functions[$name];

        $ref = new ReflectionFunction($function);

        if ($ref->getNumberOfRequiredParameters() > count($params)) {
            throw new Exception("Wrong required parameter count");
        }

        return $ref->invokeArgs($params);
    }
}
