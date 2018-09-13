<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace GeneralDataProtection;

/**
 * declares all GDPR-timer-intervals
 * in "number of days"
 */
abstract class Intervals
{
    /**
     * timer-names and there intervals ("each X days")
     * @var array
     */
    const vTIMERS = [
        // lets the task executed at each run
        // (can understood as a placeholder)
        'INTERVAL_DAYS_0'   => 0,

        // a 7 days interval
        'INTERVAL_DAYS_7'   => 7,

        // delete newsletter-registrations with no opt-in within given interval
        // (former "interval_clear_non_opt_newsletter_recipients")
        'INTERVAL_DAYS_30'  => 30,

        // delete old logs containing personal data.
        // customer-history-logs will be deleted at the end of the following year after log-creation according to german law § 76 BDSG (neu)
        // (former "interval_clear_logs")
        'INTERVAL_DAYS_90'  => 90,

        // remove guest accounts fetched by JTL-Wawi and older than x days
        // (former "interval_delete_guest_accounts")
        'INTERVAL_DAYS_365' => 365
    ];

}
