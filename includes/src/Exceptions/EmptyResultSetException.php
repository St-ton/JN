<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Exceptions;

use Exception;

/**
 * Class EmptyResultSetException
 * @package JTL\Exceptions
 */
class EmptyResultSetException extends Exception
{
    /**
     * EmptyResultSetException constructor.
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct(\str_replace(\PFAD_ROOT, '', $this->file) . ': ' . $message);
    }
}
