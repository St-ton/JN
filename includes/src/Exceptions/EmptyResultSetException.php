<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Exceptions;

/**
 * Class EmptyResultSetException
 * @package Exceptions
 */
class EmptyResultSetException extends \Exception
{
    /**
     * EmptyResultSetException constructor.
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($this->file.': '.$message);
    }
}
