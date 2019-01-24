<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class NetSyncResponse
 */
class NetSyncResponse
{
    public const UNKNOWN          = -1;
    public const OK               = 0;
    public const ERRORLOGIN       = 1;
    public const ERRORDESERIALIZE = 2;
    public const RECEIVINGDATA    = 3;
    public const FOLDERNOTEXISTS  = 4;
    public const ERRORINTERNAL    = 5;
    public const ERRORNOLICENSE   = 6;
}
