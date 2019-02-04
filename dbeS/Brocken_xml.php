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
        $input = StringHandler::filterXSS($_POST['b']);
        $data  = Shop::Container()->getDB()->query(
            'SELECT cBrocken
                FROM tbrocken
                ORDER BY dErstellt DESC
                LIMIT 1',
            \DB\ReturnType::SINGLE_OBJECT
        );
        if (empty($data->cBrocken)) {
            $data            = new stdClass();
            $data->cBrocken  = $input;
            $data->dErstellt = 'NOW()';
            Shop::Container()->getDB()->insert('tbrocken', $data);
        } elseif (isset($data->cBrocken) && $data->cBrocken !== $input && strlen($data->cBrocken) > 0) {
            Shop::Container()->getDB()->update(
                'tbrocken',
                'cBrocken',
                $data->cBrocken,
                (object)['cBrocken' => $input, 'dErstellt' => 'NOW()']
            );
        }
        $return = 0;
        Shop::Container()->getCache()->flushTags([CACHING_GROUP_CORE]);
    }
}

echo $return;
