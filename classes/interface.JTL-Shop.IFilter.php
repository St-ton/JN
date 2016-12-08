<?php

/**
 * Interface IMedia
 */
interface IFilter
{
    /**
     * @param int $id
     * @return $this
     */
    public function setID($id);

    /**
     * @return int
     */
    public function getID();

    /**
     * @param array $languages
     * @return $this
     */
    public function setSeo($languages);

    /**
     * @return string
     */
    public function getTableName();

    /**
     * @return string
     */
    public function getPrimaryKeyRow();

    /**
     * @return string
     */
    public function getSQLCondition();

    /**
     * @return FilterJoin[]
     */
    public function getSQLJoin();

    /**
     * @param mixed|null $mixed
     * @return []
     */
    public function getOptions($mixed);
}
