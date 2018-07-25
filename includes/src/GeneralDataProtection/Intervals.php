<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GdprAnonymizing;

abstract class Intervals
{
    /**
     * this should be the lowest time-period, in which we do any anonymization-stuff
     * ("Runner-period" in days)
     * @const int
     */
    const TIMER_RESET = 7;



    /**
     * Delete newsletter-registrations with no opt-in within given interval
     * @const int
     */
    const TC_DAYS_30  = 30; // "interval_clear_non_opt_newsletter_recipients"

    /**
     * delete old logs containing personal data.
     * @const int
     */
    const TC_DAYS_90  = 90; // "interval_clear_logs"

    /**
     * remove guest accounts fetched by JTL-Wawi and older than x days
     * @const int
     */
    const TC_DAYS_365 = 365; // "interval_delete_guest_accounts"

}
