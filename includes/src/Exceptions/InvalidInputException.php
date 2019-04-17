<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Exceptions;

use Exception;

/**
 * Class InvalidInputException
 * @package JTL\Exceptions
 */
class InvalidInputException extends Exception
{
    /**
     * InvalidInputException constructor.
     * @param string $message
     * @param string $origInput
     */
    public function __construct(string $message, string $origInput = '')
    {
        parent::__construct($message . ' (' . $origInput . ')');
    }
}
