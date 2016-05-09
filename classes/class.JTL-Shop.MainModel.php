<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MainModel
 *
 * @access public
 * @author Daniel Böhmer
 * @copyright 2012 JTL-Software GmbH
 */
abstract class MainModel
{
    /**
     * @param null $kKey
     * @param null $oObj
     * @param null $xOption
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
     * @param      $kKey
     * @param null $oObj
     * @param null $xOption
     * @return mixed
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
            if (in_array($method, $methods)) {
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
        $oObj        = new stdClass();
        $cMember_arr = array_keys(get_object_vars($this));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $cMethod = "get" . substr($cMember, 1);
                if (method_exists($this, $cMethod)) {
                    $oObj->{$cMember} = call_user_func(array(&$this, $cMethod));
                }
            }
        }

        return json_encode($oObj);
    }

    /**
     * @return string
     */
    public function toCSV()
    {
        $cMember_arr = array_keys(get_object_vars($this));
        $cCSV        = '';
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $i => $cMember) {
                $cMethod = 'get' . substr($cMember, 1);
                if (method_exists($this, $cMethod)) {
                    $cSep = '';
                    if ($i > 0) {
                        $cSep = ';';
                    }

                    $cCSV .= $cSep . call_user_func(array(&$this, $cMethod));
                }
            }
        }

        return $cCSV;
    }

    /**
     * @param array $Nonpublics
     * @return stdClass
     */
    public function getPublic(array $Nonpublics)
    {
        $Obj = new stdClass();

        $members = array_keys(get_object_vars($this));
        if (is_array($members) && count($members) > 0) {
            foreach ($members as $member) {
                if (!in_array($member, $Nonpublics)) {
                    $Obj->$member = $this->$member;
                }
            }
        }

        return $Obj;
    }

    /**
     * @param $oObj
     */
    public function loadObject($oObj)
    {
        $cMember_arr = array_keys(get_object_vars($oObj));
        if (is_array($cMember_arr) && count($cMember_arr) > 0) {
            foreach ($cMember_arr as $cMember) {
                $cMethod = 'set' . substr($cMember, 1);
                if (method_exists($this, $cMethod)) {
                    call_user_func(array(&$this, $cMethod), $oObj->$cMember);
                }
            }
        }
    }
}
