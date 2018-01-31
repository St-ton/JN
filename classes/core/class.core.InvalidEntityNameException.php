<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class InvalidEntityNameException
 */
class InvalidEntityNameException extends Exception
{
    /**
     * @var string
     */
    protected $entityName;

    /**
     * InvalidEntityNameException constructor.
     * @param string $entityName
     */
    public function __construct($entityName)
    {
        $this->entityName = $entityName;
        parent::__construct('Invalid entity name ' . $entityName);
    }
}
