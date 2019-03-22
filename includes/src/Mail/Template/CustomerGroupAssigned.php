<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Template;

/**
 * Class CustomerGroupAssigned
 * @package JTL\Mail\Template
 */
class CustomerGroupAssigned extends CustomerAccountDeleted
{
    protected $id = \MAILTEMPLATE_KUNDENGRUPPE_ZUWEISEN;
}
