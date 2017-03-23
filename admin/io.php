<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/includes/admininclude.php';

$io = IO::getInstance();

function getProducts($limit)
{
    $items = Shop::DB()->query(
        "SELECT cName
            FROM tartikel",
        2
    );

    return $items;
}

$obj = (object)[
    'foo' => 42,
    'bar' => 'Hello',
];

$obj = json_encode($obj);
$obj = "Hello WOrld";

if (is_string($obj)) {
    json_decode($obj);
    $res = json_last_error() !== JSON_ERROR_NONE;
    if ($res) {
        $obj = json_encode($obj);
    }
} else {
    $obj = json_encode($obj);
}

var_dump($obj);

//$io->register('getProducts', ['JSONAPI', 'getProducts']);
//$io->handleRequest($_REQUEST['io']);
