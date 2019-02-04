<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once __DIR__ . '/syncinclude.php';

// wawi mindestversion überprüfen
if (!isset($_POST['wawiversion']) || (int)$_POST['wawiversion'] < JTL_MIN_WAWI_VERSION) {
    syncException(
        'Ihr JTL-Shop Version ' . APPLICATION_VERSION .
        ' benötigt für den Datenabgleich mindestens JTL-Wawi Version ' . (JTL_MIN_WAWI_VERSION / 100000.0) .
        ". \nEine aktuelle Version erhalten Sie unter: https://jtl-url.de/wawidownload",
        FREIDEFINIERBARER_FEHLER
    );
}
$return = 3;
$user   = utf8_encode($_POST['uID']);
$pass   = utf8_encode($_POST['uPWD']);

$_POST['uID']  = '*';
$_POST['uPWD'] = '*';

$login      = new \dbeS\Synclogin();
$versionStr = null;
if ($login->checkLogin($user, $pass) === true) {
    $return = 0;
    $db     = Shop::Container()->getDB();
    if (isset($_POST['kKunde']) && (int)$_POST['kKunde'] > 0) {
        $state = $db->query(
            "SHOW TABLE STATUS LIKE 'tkunde'",
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($state->Auto_increment < (int)$_POST['kKunde']) {
            $db->query(
                'ALTER TABLE tkunde AUTO_INCREMENT = ' . (int)$_POST['kKunde'],
                \DB\ReturnType::DEFAULT
            );
        }
    }
    if (isset($_POST['kBestellung']) && (int)$_POST['kBestellung'] > 0) {
        $state = $db->query(
            "SHOW TABLE STATUS LIKE 'tbestellung'",
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($state->Auto_increment < (int)$_POST['kBestellung']) {
            $db->query(
                'ALTER TABLE tbestellung AUTO_INCREMENT = ' . (int)$_POST['kBestellung'],
                \DB\ReturnType::DEFAULT
            );
        }
    }
    if (isset($_POST['kLieferadresse']) && (int)$_POST['kLieferadresse'] > 0) {
        $state = $db->query(
            "SHOW TABLE STATUS LIKE 'tlieferadresse'",
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($state->Auto_increment < (int)$_POST['kLieferadresse']) {
            $db->query(
                'ALTER TABLE tlieferadresse AUTO_INCREMENT = ' . (int)$_POST['kLieferadresse'],
                \DB\ReturnType::DEFAULT
            );
        }
    }
    if (isset($_POST['kZahlungseingang']) && (int)$_POST['kZahlungseingang'] > 0) {
        $state = $db->query(
            "SHOW TABLE STATUS LIKE 'tzahlungseingang'",
            \DB\ReturnType::SINGLE_OBJECT
        );
        if ($state->Auto_increment < (int)$_POST['kZahlungseingang']) {
            $db->query(
                'ALTER TABLE tzahlungseingang AUTO_INCREMENT  = ' . (int)$_POST['kZahlungseingang'],
                \DB\ReturnType::DEFAULT
            );
        }
    }
    $version    = Shop::getShopDatabaseVersion();
    $versionStr = sprintf('%d%02d', $version->getMajor(), $version->getMinor());
} else {
    syncException((string)$return);
}
echo $return . ';JTL4;' . $versionStr . ';';
