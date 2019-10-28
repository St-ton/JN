<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Template;

/**
 * Class OrderReactivated
 * @package JTL\Mail\Template
 */
class OrderReactivated extends OrderCleared
{
    protected $id = \MAILTEMPLATE_BESTELLUNG_RESTORNO;
}
