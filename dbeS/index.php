<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\dbeS\Synclogin;
use JTL\Shop;
use JTL\dbeS\Starter;
use JTL\dbeS\FileHandler;

require_once __DIR__ . '/syncinclude.php';

$fileID  = $_REQUEST['id'] ?? null;
$logger  = Shop::Container()->getLogService()->withName('dbeS');
$db      = Shop::Container()->getDB();
$cache   = Shop::Container()->getCache();
$starter = new Starter(new Synclogin(), new FileHandler($logger), $db, $cache, $logger);
$starter->start($fileID, $_POST, $_FILES);
