<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

//wawi mindestversion überprüfen
if (!isset($_POST['wawiversion']) || (int)$_POST['wawiversion'] < JTL_MIN_WAWI_VERSION) {
    syncException("Ihr JTL-Shop Version " .
        (JTL_VERSION / 100) . " benötigt für den Datenabgleich mindestens JTL-Wawi Version " .
        (JTL_MIN_WAWI_VERSION / 100000.0) .
        ". \nEine aktuelle Version erhalten Sie unter: https://jtl-url.de/wawidownload", 8);
}
$return = 3;
$cName  = $_POST['uID'];
$cPass  = $_POST['uPWD'];

$_POST['uID']  = '*';
$_POST['uPWD'] = '*';

$login    = new Synclogin();
$version  = '';
$oVersion = null;

if ($login->checkLogin($cName, $cPass) === true) {
    $return = 0;
    if (isset($_POST['kKunde']) && (int)$_POST['kKunde'] > 0) {
        $oStatus = Shop::Container()->getDB()->query(
            "SHOW TABLE STATUS LIKE 'tkunde'",
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($oStatus->Auto_increment < (int)$_POST['kKunde']) {
            Shop::Container()->getDB()->query(
                'ALTER TABLE tkunde AUTO_INCREMENT = ' . (int)$_POST['kKunde'], 
                \DB\ReturnType::DEFAULT
            );
        }
    }
    if (isset($_POST['kBestellung']) && (int)$_POST['kBestellung'] > 0) {
        $oStatus = Shop::Container()->getDB()->query(
            "SHOW TABLE STATUS LIKE 'tbestellung'",
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($oStatus->Auto_increment < (int)$_POST['kBestellung']) {
            Shop::Container()->getDB()->query(
                'ALTER TABLE tbestellung AUTO_INCREMENT = ' . (int)$_POST['kBestellung'], 
                \DB\ReturnType::DEFAULT
            );
        }
    }
    if (isset($_POST['kLieferadresse']) && (int)$_POST['kLieferadresse'] > 0) {
        $oStatus = Shop::Container()->getDB()->query(
            "SHOW TABLE STATUS LIKE 'tlieferadresse'",
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($oStatus->Auto_increment < (int)$_POST['kLieferadresse']) {
            Shop::Container()->getDB()->query(
                'ALTER TABLE tlieferadresse AUTO_INCREMENT = ' . (int)$_POST['kLieferadresse'],
                \DB\ReturnType::DEFAULT
            );
        }
    }
    if (isset($_POST['kZahlungseingang']) && (int)$_POST['kZahlungseingang'] > 0) {
        $oStatus = Shop::Container()->getDB()->query(
            "SHOW TABLE STATUS LIKE 'tzahlungseingang'",
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($oStatus->Auto_increment < (int)$_POST['kZahlungseingang']) {
            Shop::Container()->getDB()->query(
                'ALTER TABLE tzahlungseingang AUTO_INCREMENT  = ' . (int)$_POST['kZahlungseingang'], 
                \DB\ReturnType::DEFAULT
            );
        }
    }
    $oVersion = Shop::Container()->getDB()->query(
        'SELECT nVersion FROM tversion',
        \DB\ReturnType::SINGLE_OBJECT
    );
} else {
    syncException("{$return}");
}
echo $return . ';JTL4;' . $oVersion->nVersion . ';';
