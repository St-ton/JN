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
     * ctor
     */
    private function __construct() { }

    /**
     * copy-ctor
     */
    private function __clone() { }

    /**
     * @return self
     */
    public static function getInstance()
    {
        return self::$instance === null ? (self::$instance = new self()) : self::$instance;
    }

    /**
     * Registers a PHP function or method.
     * This makes the function available for XMLHTTPRequest requests.
     *
     * @param string        $name
     * @param null|callable $function
     * @param null|string   $include
     * @param null|string   $permission
     * @return $this
     * @throws Exception
     */
    public function register($name, $function = null, $include = null, $permission = null)
    {
        if ($this->exists($name)) {
            throw new Exception("Function already registered");
        }

        if ($function === null) {
            $function = $name;
        }

        $this->functions[$name] = [$function, $include, $permission];

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

    /**
     * @param $data
     */
    public function respondAndExit($data)
    {
        // encode data if not already encoded
        if (is_string($data)) {
            // data is a string
            json_decode($data);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // it is not a JSON string yet
                $data = json_encode($data);
            }
        } elseif (is_null($data)) {
            $data = '{}';
        } else {
            $data = json_encode($data);
        }

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-type: application/json');

        die($data);
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
        global $oAccount;

        if (!$this->exists($name)) {
            throw new Exception("Function not registered");
        }

        $function   = $this->functions[$name][0];
        $include    = $this->functions[$name][1];
        $permission = $this->functions[$name][2];

        if ($include !== null) {
            require_once $include;
        }

        if (is_array($function)) {
            $ref = new ReflectionMethod($function[0], $function[1]);
        } else {
            $ref = new ReflectionFunction($function);
        }

        if ($ref->getNumberOfRequiredParameters() > count($params)) {
            throw new Exception("Wrong required parameter count");
        }

        if ($permission !== null && !$oAccount->permission($permission)) {
            throw new Exception("User has not the required permission to execute this function");
        }

        return call_user_func_array($function, $params);
    }
}
