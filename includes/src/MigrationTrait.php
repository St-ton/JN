<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
trait MigrationTrait
{
    /**
     * executes query and returns misc data
     *
     * @param string   $query - Statement to be executed
     * @param int      $return - what should be returned.
     * @param int|bool $echo print current stmt
     * @param bool     $bExecuteHook should function executeHook be executed
     * 1  - single fetched object
     * 2  - array of fetched objects
     * 3  - affected rows
     * 8  - fetched assoc array
     * 9  - array of fetched assoc arrays
     * 10 - result of querysingle
     * @return array|object|int - 0 if fails, 1 if successful or LastInsertID if specified
     * @throws InvalidArgumentException
     */
    protected function __execute($query, $return, $echo = false, $bExecuteHook = false)
    {
        if (JTL_CHARSET === 'iso-8859-1') {
            $query = utf8_convert_recursive($query, false);
        }

        return Shop::Container()->getDB()->executeQuery($query, $return, $echo, $bExecuteHook);
    }

    /**
     * @param $query
     * @param bool $echo
     * @param bool $bExecuteHook
     * @return array|object|int
     */
    public function execute($query, $echo = false, $bExecuteHook = false)
    {
        return $this->__execute($query, \DB\ReturnType::AFFECTED_ROWS, $echo, $bExecuteHook);
    }

    /**
     * @param $query
     * @param bool $echo
     * @param bool $bExecuteHook
     * @return array|object|int
     */
    public function fetchOne($query, $echo = false, $bExecuteHook = false)
    {
        return $this->__execute($query, \DB\ReturnType::SINGLE_OBJECT, $echo, $bExecuteHook);
    }

    /**
     * @param $query
     * @param bool $echo
     * @param bool $bExecuteHook
     * @return array|object|int
     */
    public function fetchAll($query, $echo = false, $bExecuteHook = false)
    {
        return $this->__execute($query, \DB\ReturnType::ARRAY_OF_OBJECTS, $echo, $bExecuteHook);
    }

    /**
     * @param $query
     * @param bool $echo
     * @param bool $bExecuteHook
     * @return array|object|int
     */
    public function fetchArray($query, $echo = false, $bExecuteHook = false)
    {
        return $this->__execute($query, \DB\ReturnType::ARRAY_OF_ASSOC_ARRAYS, $echo, $bExecuteHook);
    }
}
