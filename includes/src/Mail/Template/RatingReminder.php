<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Mail\Template;

/**
 * Class RatingReminder
 * @package JTL\Mail\Template
 */
class RatingReminder extends OrderShipped
{
    protected $id = \MAILTEMPLATE_BEWERTUNGERINNERUNG;
}
