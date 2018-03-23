<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace DB;

abstract class ReturnType
{
    const DEFAULT = 4;
    const SINGLE_OBJECT = 1;
    const ARRAY_OF_OBJECTS = 2;
    const AFFECTED_ROWS = 3;
    const LAST_INSERTED_ID = 7;
    const SINGLE_ASSOC_ARRAY = 8;
    const ARRAY_OF_ASSOC_ARRAYS = 9;
    const QUERYSINGLE = 10;
    const ARRAY_OF_BOTH_ARRAYS = 11;
}
