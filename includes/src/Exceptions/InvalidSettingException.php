<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Exceptions;

use Exception;

/**
 * Class InvalidSettingException
 * @package JTL\Exceptions
 */
class InvalidSettingException extends Exception
{
    /**
     * InvalidSettingException constructor.
     * @param $message
     */
    public function __construct($message)
    {
        parent::__construct('Einstellungsfehler: ' . $message);
    }
}
