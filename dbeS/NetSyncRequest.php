<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class NetSyncRequest
 */
class NetSyncRequest
{
    public const UNKNOWN               = 0;
    public const UPLOADFILES           = 1;
    public const UPLOADFILEDATA        = 2;
    public const DOWNLOADFOLDERS       = 3;
    public const DOWNLOADFILESINFOLDER = 4;
    public const CRONJOBTRIGGER        = 5;
    public const CRONJOBSTATUS         = 6;
    public const CRONJOBHISTORY        = 7;
}
