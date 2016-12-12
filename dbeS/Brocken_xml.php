<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once dirname(__FILE__) . '/syncinclude.php';

$return = 3;
if (auth()) {
    $return = 2;
    if (isset($_POST['b']) && strlen($_POST['b']) > 0) {
        $cBrocken = StringHandler::filterXSS($_POST['b']);    // Wawi Brocken
        // Schau ob bereits Brocken vorhanden und ob sich Brocken geändert hat
        $oBrocken = Shop::DB()->query(
            "SELECT cBrocken
                FROM tbrocken
                ORDER BY dErstellt DESC
                LIMIT 1", 1
        );
        // Leer?
        if (!isset($oBrocken->cBrocken) || strlen($oBrocken->cBrocken) === 0) {
            // Insert
            $oBrocken            = new stdClass();
            $oBrocken->cBrocken  = $cBrocken;
            $oBrocken->dErstellt = 'now()';

            Shop::DB()->insert('tbrocken', $oBrocken);
        } elseif (isset($oBrocken->cBrocken) && strlen($oBrocken->cBrocken) > 0 && $oBrocken->cBrocken != $cBrocken) { // Verändert?
            // Update
            Shop::DB()->update('tbrocken', 'cBrocken', $oBrocken->cBrocken, (object)['cBrocken' => $cBrocken, 'dErstellt' => 'now()']);
        }
        $return = 0;
        Shop::Cache()->flushTags([CACHING_GROUP_CORE]);
    }
}

echo $return;
