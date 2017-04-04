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
     * @var static
     */
    protected static $instance = null;

    /**
     * @var array
     */
    protected $functions = [];

    /**
     * ctor
     */
    private function __construct() { }

    /**
     * copy-ctor
     */
    private function __clone() { }

    /**
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance === null ? (static::$instance = new static()) : static::$instance;
    }

    /**
     * Registers a PHP function or method.
     * This makes the function available for XMLHTTPRequest requests.
     *
     * @param string        $name - name udner which this function is callable
     * @param null|callable $function - target function name, method-tuple or closure
     * @param null|string   $include - file where this function is defined in
     * @return $this
     * @throws Exception
     */
    public function register($name, $function = null, $include = null)
    {
        if ($this->exists($name)) {
            throw new Exception("Function already registered");
        }

        if ($function === null) {
            $function = $name;
        }

        $this->functions[$name] = [$function, $include];

        return $this;
    }

    /**
     * @param string $reqString
     * @return mixed
     */
    public function handleRequest($reqString)
    {
        $request = json_decode($reqString, true);

        if (($errno = json_last_error()) != JSON_ERROR_NONE) {
            return new IOError("Error {$errno} while decoding data");
        }

        if (!isset($request['name'], $request['params'])) {
            return new IOError("Missing request property");
        }

        return $this->execute($request['name'], $request['params']);
    }

    /**
     * @param $data
     */
    public function respondAndExit($data)
    {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-type: application/json');

        // respond with an error?
        if (is_object($data) && get_class($data) === 'IOError') {
            header(makeHTTPHeader($data->code), true, $data->code);
        }

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
        if (!$this->exists($name)) {
            return new IOError("Function not registered");
        }

        $function = $this->functions[$name][0];
        $include  = $this->functions[$name][1];

        if ($include !== null) {
            require_once $include;
        }

        if (is_array($function)) {
            $ref = new ReflectionMethod($function[0], $function[1]);
        } else {
            $ref = new ReflectionFunction($function);
        }

        if ($ref->getNumberOfRequiredParameters() > count($params)) {
            return new IOError("Wrong required parameter count");
        }

        return call_user_func_array($function, $params);
    }
}
