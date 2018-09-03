<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

$return = 3;
if (auth()) {
    $return = 2;
    if (isset($_POST['b']) && strlen($_POST['b']) > 0) {
        $cBrocken = StringHandler::filterXSS($_POST['b']);
        $oBrocken = Shop::Container()->getDB()->query(
            'SELECT cBrocken
                FROM tbrocken
                ORDER BY dErstellt DESC
                LIMIT 1',
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (empty($oBrocken->cBrocken)) {
            $oBrocken            = new stdClass();
            $oBrocken->cBrocken  = $cBrocken;
            $oBrocken->dErstellt = 'NOW()';
            Shop::Container()->getDB()->insert('tbrocken', $oBrocken);
        } elseif (isset($oBrocken->cBrocken) && $oBrocken->cBrocken !== $cBrocken && strlen($oBrocken->cBrocken) > 0) {
            Shop::Container()->getDB()->update(
                'tbrocken',
                'cBrocken',
                $oBrocken->cBrocken,
                (object)['cBrocken' => $cBrocken, 'dErstellt' => 'NOW()']
            );
        }
        $return = 0;
        Shop::Cache()->flushTags([CACHING_GROUP_CORE]);
    }
}

echo $return;
