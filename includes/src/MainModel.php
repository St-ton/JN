<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MainModel
 */
abstract class MainModel
{
    /**
     * @param null|int    $kKey
     * @param null|object $oObj
     * @param null|mixed  $xOption
     */
    public function __construct($kKey = null, $oObj = null, $xOption = null)
    {
        if (is_object($oObj)) {
            $this->loadObject($oObj);
        } elseif ($kKey !== null) {
            $this->load($kKey, $oObj, $xOption);
        }
    }

    /**
     * @param int         $kKey
     * @param null|object $oObj
     * @param null|array  $xOption
     */
    abstract public function load($kKey, $oObj = null, $xOption = null);

    /**
     * @return array
     */
    public function getProperties()
    {
        return array_keys(get_object_vars($this));
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods, true)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function toJSON()
    {
        $item = new stdClass();
        foreach (array_keys(get_object_vars($this)) as $member) {
            $method = 'get' . mb_substr($member, 1);
            if (method_exists($this, $method)) {
                $item->$member = $this->$method();
            }
        }

        return json_encode($item);
    }

    /**
     * @return string
     */
    public function toCSV()
    {
        $csv = '';
        foreach (array_keys(get_object_vars($this)) as $i => $member) {
            $method = 'get' . mb_substr($member, 1);
            if (method_exists($this, $method)) {
                $sep = '';
                if ($i > 0) {
                    $sep = ';';
                }

                $csv .= $sep . $this->$method();
            }
        }

        return $csv;
    }

    /**
     * @param array $nonpublics
     * @return stdClass
     */
    public function getPublic(array $nonpublics)
    {
        $item = new stdClass();
        foreach (array_keys(get_object_vars($this)) as $member) {
            if (!in_array($member, $nonpublics, true)) {
                $item->$member = $this->$member;
            }
        }

        return $item;
    }

    /**
     * @param object $obj
     */
    public function loadObject($obj)
    {
        foreach (array_keys(get_object_vars($obj)) as $member) {
            $method = 'set' . mb_substr($member, 1);
            if (method_exists($this, $method)) {
                $this->$method($obj->$member);
            }
        }
    }
}
