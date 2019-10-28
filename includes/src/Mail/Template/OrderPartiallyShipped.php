<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Template;

/**
 * Class OrderPartiallyShipped
 * @package JTL\Mail\Template
 */
class OrderPartiallyShipped extends OrderShipped
{
    protected $id = \MAILTEMPLATE_BESTELLUNG_TEILVERSANDT;
}
