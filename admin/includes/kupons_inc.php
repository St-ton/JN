<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_INCLUDES . 'mailTools.php';

/**
 * @param array $kKupon_arr
 * @return bool
 */
function loescheKupons($kKupon_arr)
{
    if (is_array($kKupon_arr) && count($kKupon_arr) > 0) {
        $kKupon_arr = array_map('intval', $kKupon_arr);
        $nRows = Shop::DB()->query(
            "DELETE
                FROM tkupon
                WHERE kKupon IN(" . implode(',', $kKupon_arr) . ")", 3
        );
        Shop::DB()->query(
            "DELETE
                FROM tkuponsprache
                WHERE kKupon IN(" . implode(',', $kKupon_arr) . ")", 3
        );

        return ($nRows >= count($kKupon_arr));
    }

    return false;
}

/**
 * @param int $kKupon
 * @return array - key = lang-iso ; value = localized coupon name
 */
function getCouponNames($kKupon)
{
    $namen = array();
    if (!$kKupon) {
        return $namen;
    }
    $kuponnamen = Shop::DB()->query("SELECT * FROM tkuponsprache WHERE kKupon = " . (int)$kKupon, 2);
    $kCount     = count($kuponnamen);
    for ($i = 0; $i < $kCount; $i++) {
        $namen[$kuponnamen[$i]->cISOSprache] = $kuponnamen[$i]->cName;
    }

    return $namen;
}

/**
 * @param string $selKats
 * @param int    $kKategorie
 * @param int    $tiefe
 * @return array
 */
function getCategories($selKats = '', $kKategorie = 0, $tiefe = 0)
{
    $selected = StringHandler::parseSSK($selKats);
    $arr      = array();
    $kats     = Shop::DB()->query("SELECT kKategorie, cName FROM tkategorie WHERE kOberKategorie = " . (int)$kKategorie, 2);
    $kCount   = count($kats);
    for ($o = 0; $o < $kCount; $o++) {
        for ($i = 0; $i < $tiefe; $i++) {
            $kats[$o]->cName = '--' . $kats[$o]->cName;
        }
        $kats[$o]->selected = 0;
        if (in_array($kats[$o]->kKategorie, $selected)) {
            $kats[$o]->selected = 1;
        }
        $arr[] = $kats[$o];
        $arr   = array_merge($arr, getCategories($selKats, $kats[$o]->kKategorie, $tiefe + 1));
    }

    return $arr;
}

/**
 * @param string $selKats
 * @param int    $kKategorie
 * @param int    $tiefe
 * @return array
 */
function getCustomers($selCustomers = '')
{
    $selected    = StringHandler::parseSSK($selCustomers);
    $customers   = Shop::DB()->query("SELECT kKunde FROM tkunde", 2);

    foreach ($customers as $i => $customer) {
        $oKunde                     = new Kunde($customer->kKunde);
        $customers[$i]->cVorname    = $oKunde->cVorname;
        $customers[$i]->cNachname   = $oKunde->cNachname;
        $customers[$i]->selected    = in_array($customers[$i]->kKunde, $selected) ? 1 : 0;
        unset($oKunde);
    }

    return $customers;
}

/**
 * Parse Datumsstring und formatiere ihn im DB-kompatiblen Standardformat
 * 
 * @param string $string
 * @return string
 */
function normalizeDate($string)
{
    if ($string === null || $string === '') {
        return '0000-00-00 00:00:00';
    }

    $date = date_create($string);

    if ($date === false) {
        return $string;
    }

    return $date->format('Y-m-d H:i') . ':00';
}

/**
 * Get instances of existing coupons, each with some enhanced information that can be displayed
 * 
 * @param string $cKuponTyp
 * @param string $cLimitSQL - an SQL LIMIT clause
 * @param string $cOrderBy - a column that should be sorted by
 * @return array
 */
function getCoupons($cKuponTyp = 'standard', $cLimitSQL = '', $cOrderBy = 'kKupon')
{
    $oKuponDB_arr = Shop::DB()->query("
        SELECT kKupon
            FROM tkupon
            WHERE cKuponTyp = '" . $cKuponTyp . "'
            ORDER BY " . $cOrderBy . " " .
            $cLimitSQL,
        2);
    $oKupon_arr = array();

    foreach ($oKuponDB_arr as $oKuponDB) {
        $oKupon_arr[] = getCoupon((int)$oKuponDB->kKupon);
    }

    return $oKupon_arr;
}

/**
 * Get an instance of an existing coupon with some enhanced information that can be displayed
 *
 * @param int $kKupon
 * @return Kupon $oKupon
 */
function getCoupon($kKupon)
{
    $oKupon = new Kupon($kKupon);
    augmentCoupon($oKupon);

    return $oKupon;
}

/**
 * Enhance an existing Kupon instance with some extra information that can be displayed
 *
 * @param Kupon $oKupon
 */
function augmentCoupon($oKupon)
{
    $oKupon->bOpenEnd = $oKupon->dGueltigBis === '0000-00-00 00:00:00';

    if (date_create($oKupon->dGueltigAb) === false) {
        $oKupon->cGueltigAbShort = 'ung&uuml;ltig';
        $oKupon->cGueltigAbLong  = 'ung&uuml;ltig';
    } else {
        $oKupon->cGueltigAbShort = date_create($oKupon->dGueltigAb)->format('d.m.Y');
        $oKupon->cGueltigAbLong  = date_create($oKupon->dGueltigAb)->format('d.m.Y H:i');
    }

    if ($oKupon->bOpenEnd) {
        $oKupon->cGueltigBisShort = 'open-end';
        $oKupon->cGueltigBisLong  = 'open-end';
    } elseif (date_create($oKupon->dGueltigBis) === false) {
        $oKupon->cGueltigBisShort = 'ung&uuml;ltig';
        $oKupon->cGueltigBisLong  = 'ung&uuml;ltig';
    } else {
        $oKupon->cGueltigBisShort  = date_create($oKupon->dGueltigBis)->format('d.m.Y');
        $oKupon->cGueltigBisLong   = date_create($oKupon->dGueltigBis)->format('d.m.Y H:i');
    }

    if ((int)$oKupon->kKundengruppe == -1) {
        $oKupon->cKundengruppe = 'Alle';
    } else {
        $oKundengruppe         = Shop::DB()->query("SELECT cName FROM tkundengruppe WHERE kKundengruppe = " . $oKupon->kKundengruppe, 1);
        $oKupon->cKundengruppe = $oKundengruppe->cName;
    }

    if ($oKupon->cArtikel === '') {
        $oKupon->ArtikelInfo = 'Alle';
    } else {
        $oKupon->ArtikelInfo = 'eingeschr&auml;nkt';
    }
}

/**
 * Create a fresh Kupon instance with default values to be edited
 * 
 * @param $cKuponTyp - 'standard', 'versandkupon', 'neukundenkupon'
 * @return Kupon
 */
function createNewCoupon($cKuponTyp)
{
    $oKupon                        = new Kupon();
    $oKupon->cKuponTyp             = $cKuponTyp;
    $oKupon->cName                 = 'neuerkupon';
    $oKupon->fWert                 = 0.0;
    $oKupon->cWertTyp              = 'festpreis';
    $oKupon->cZusatzgebuehren      = 'N';
    $oKupon->nGanzenWKRabattieren  = 1;
    $oKupon->kSteuerklasse         = 1;
    $oKupon->fMindestbestellwert   = 0.0;
    $oKupon->cCode                 = $oKupon->generateCode();
    $oKupon->cLieferlaender        = '';
    $oKupon->nVerwendungen         = 1;
    $oKupon->nVerwendungenProKunde = 1;
    $oKupon->cArtikel              = '';
    $oKupon->kKundengruppe         = -1;
    $oKupon->dGueltigAb            = date_create()->format('Y-m-d H:i');
    $oKupon->dGueltigBis           = $oKupon->dGueltigAb;
    $oKupon->cAktiv                = 'Y';
    $oKupon->cKategorien           = '-1';
    $oKupon->cKunden               = '-1';
    $oKupon->kKupon                = 0;

    augmentCoupon($oKupon);

    return $oKupon;
}

/**
 * Read coupon settings from the edit page form and create a Kupon instance of it
 * 
 * @return Kupon
 */
function createCouponFromInput()
{
    $oKupon                        = new Kupon((int)$_POST['kKuponBearbeiten']);
    $oKupon->cKuponTyp             = $_POST['cKuponTyp'];
    $oKupon->cName                 = $_POST['cName'];
    $oKupon->fWert                 = isset($_POST['fWert']) ? (float)str_replace(',', '.', $_POST['fWert']) : null;
    $oKupon->cWertTyp              = isset($_POST['cWertTyp']) ? $_POST['cWertTyp'] : null;
    $oKupon->cZusatzgebuehren      = isset($_POST['cZusatzgebuehren']) ? $_POST['cZusatzgebuehren'] : 'N';
    $oKupon->nGanzenWKRabattieren  = isset($_POST['nGanzenWKRabattieren']) ? (int)$_POST['nGanzenWKRabattieren'] : 0;
    $oKupon->kSteuerklasse         = isset($_POST['kSteuerklasse']) ? (int)$_POST['kSteuerklasse'] : null;
    $oKupon->fMindestbestellwert   = (float)str_replace(',', '.', $_POST['fMindestbestellwert']);
    $oKupon->cCode                 = isset($_POST['cCode']) ? $_POST['cCode'] : '';
    $oKupon->cLieferlaender        = isset($_POST['cLieferlaender']) ? strtoupper($_POST['cLieferlaender']) : '';
    $oKupon->nVerwendungen         = isset($_POST['nVerwendungen']) ? (int)$_POST['nVerwendungen'] : 0;
    $oKupon->nVerwendungenProKunde = isset($_POST['nVerwendungenProKunde']) ? (int)$_POST['nVerwendungenProKunde'] : 0;
    $oKupon->cArtikel              = trim($_POST['cArtikel']);
    $oKupon->kKundengruppe         = (int)$_POST['kKundengruppe'];
    $oKupon->dGueltigAb            = normalizeDate(isset($_POST['dGueltigAb']) ? $_POST['dGueltigAb'] : null);
    $oKupon->dGueltigBis           = normalizeDate(isset($_POST['dGueltigBis']) ? $_POST['dGueltigBis'] : null);
    $oKupon->cAktiv                = isset($_POST['cAktiv']) && $_POST['cAktiv'] === 'Y' ? 'Y' : 'N';
    $oKupon->cKategorien           = '-1';
    $oKupon->cKunden               = '-1';

    if ($oKupon->dGueltigAb === '0000-00-00 00:00:00') {
        $oKupon->dGueltigAb = date_create()->format('Y-m-d H:i') . ':00';
    }
    if (isset($_POST['bOpenEnd']) && $_POST['bOpenEnd'] === 'Y') {
        $oKupon->dGueltigBis = '0000-00-00 00:00:00';
    }
    if ($oKupon->cKuponTyp !== 'neukundenkupon' && $oKupon->cCode === '') {
        $oKupon->cCode = $oKupon->generateCode();
    }
    if (isset($_POST['kKategorien']) && is_array($_POST['kKategorien']) && count($_POST['kKategorien']) > 0 && !in_array('-1', $_POST['kKategorien'])) {
        $oKupon->cKategorien = StringHandler::createSSK($_POST['kKategorien']);
    }
    if (isset($_POST['kKunden']) && is_array($_POST['kKunden']) && count($_POST['kKunden']) > 0 && !in_array('-1', $_POST['kKunden'])) {
        $oKupon->cKunden = StringHandler::createSSK($_POST['kKunden']);
    }

    return $oKupon;
}

/**
 * Get the number of existing coupons of type $cKuponTyp
 * 
 * @param string $cKuponTyp
 * @return int
 */
function getCouponCount($cKuponTyp = 'standard')
{
    $oKuponDB = Shop::DB()->query("
        SELECT count(kKupon) AS count
            FROM tkupon
            WHERE cKuponTyp = '" . $cKuponTyp . "'",
        1);

    return $oKuponDB->count;
}

/**
 * Validates the fields of a given Kupon instance
 * 
 * @param Kupon $oKupon
 * @return array - list of error messages
 */
function validateCoupon($oKupon)
{
    $cFehler_arr = array();

    if ($oKupon->cName === '') {
        $cFehler_arr[] = 'Es wurde kein Kuponname angegeben. Bitte geben Sie einen Namen an!';
    }
    if (($oKupon->cKuponTyp === 'standard' || $oKupon->cKuponTyp === 'neukundenkupon') && $oKupon->fWert < 0) {
        $cFehler_arr[] = 'Bitte geben Sie einen nicht-negativen Kuponwert an!';
    }
    if ($oKupon->fMindestbestellwert < 0) {
        $cFehler_arr[] = 'Bitte geben Sie einen nicht-negativen Mindestbestellwert an!';
    }
    if ($oKupon->cKuponTyp === 'versandkupon' && $oKupon->cLieferlaender === '') {
        $cFehler_arr[] = 'Bitte geben Sie die L&auml;nderk&uuml;rzel (ISO-Codes) unter "Lieferl&auml;nder" an, f&uuml;r die dieser Versandkupon gelten soll!';
    }
    if ($oKupon->cKuponTyp == 'standard' || $oKupon->cKuponTyp === 'versandkupon') {
        $queryRes = Shop::DB()->query("
        SELECT kKupon
            FROM tkupon
            WHERE cCode = '" . $oKupon->cCode . "'" . ((int)$oKupon->kKupon > 0 ? " AND kKupon != " . (int)$oKupon->kKupon : ''),
            1);
        if (is_object($queryRes)) {
            $cFehler_arr[] = 'Der angegeben Kuponcode wird bereits von einem anderen Kupon verwendet. Bitte w&auml;hlen Sie einen anderen Code!';
        }
    }

    $cArtNr_arr = StringHandler::parseSSK($oKupon->cArtikel);
    foreach ($cArtNr_arr as $cArtNr) {
        $res = Shop::DB()->select('tartikel', 'cArtNr', $cArtNr);
        if ($res === null) {
            $cFehler_arr[] = 'Die Artikelnummer "' . $cArtNr . '" geh&ouml;rt zu keinem g&uuml;ltigen Artikel.';
        }
    }

    if ($oKupon->cKuponTyp === 'versandkupon') {
        $cLandISO_arr = StringHandler::parseSSK($oKupon->cLieferlaender);
        foreach ($cLandISO_arr as $cLandISO) {
            $res = Shop::DB()->select('tland', 'cISO', $cLandISO);
            if ($res === null) {
                $cFehler_arr[] = 'Der ISO-Code "' . $cLandISO . '" geh&ouml;rt zu keinem g&uuml;ltigen Land.';
            }
        }
    }

    $dGueltigAb  = date_create($oKupon->dGueltigAb);
    $dGueltigBis = date_create($oKupon->dGueltigBis);

    if ($dGueltigAb === false) {
        $cFehler_arr[] = 'Bitte geben sie den Beginn des G&uuml;ltigkeitszeitraumes im Format (<strong>tt.mm.yyyy ss:mm</strong>) an!';
    }
    if ($dGueltigBis === false) {
        $cFehler_arr[] = 'Bitte geben sie das Ende des G&uuml;ltigkeitszeitraumes im Format (<strong>tt.mm.yyyy ss:mm</strong>) an!';
    }

    $bOpenEnd = $oKupon->dGueltigBis === '0000-00-00 00:00:00';

    if ($dGueltigAb !== false && $dGueltigBis !== false && $dGueltigAb > $dGueltigBis && $bOpenEnd === false) {
        $cFehler_arr[] = 'Das Ende des G&uuml;ltigkeitszeitraumes muss nach dem Beginn des G&uuml;ltigkeitszeitraumes liegen!';
    }

    return $cFehler_arr;
}

/**
 * Save a new or already existing coupon in the DB
 * 
 * @param Kupon $oKupon
 * @param array $oSprache_arr
 * @return int - 0 on failure ; kKupon on success
 */
function saveCoupon($oKupon, $oSprache_arr)
{
    if ((int)$oKupon->kKupon > 0) {
        // vorhandener Kupon
        $res = $oKupon->update() === -1 ? 0 : $oKupon->kKupon;
    } else {
        echo "<pre> neuer Kupon unter saveCoupon </pre>";
        // neuer Kupon
        $oKupon->nVerwendungenBisher = 0;
        $oKupon->dErstellt           = 'now()';
        $oKupon->kKupon              = (int)$oKupon->save();
        $res                         = $oKupon->kKupon;
    }

    if ($res > 0) {echo "<pre> neuer Kupon unter saveCoupon mit res </pre>";
        // Kupon-Sprachen aktualisieren
        Shop::DB()->delete('tkuponsprache', 'kKupon', $oKupon->kKupon);

        foreach ($oSprache_arr as $oSprache) {
            $cKuponSpracheName =
                (isset($_POST['cName_' . $oSprache->cISO]) && $_POST['cName_' . $oSprache->cISO] !== '')
                ? $_POST['cName_' . $oSprache->cISO]
                : $oKupon->cName;

            $kuponSprache              = new stdClass();
            $kuponSprache->kKupon      = $oKupon->kKupon;
            $kuponSprache->cISOSprache = $oSprache->cISO;
            $kuponSprache->cName       = $cKuponSpracheName;
            Shop::DB()->insert('tkuponsprache', $kuponSprache);
        }
    }

    return $res;
}

/**
 * Send notification emails to all customers admitted to this Kupon
 * 
 * @param Kupon $oKupon
 */
function informCouponCustomers($oKupon)
{
    // Standard-Sprache
    $oStdSprache = Shop::DB()->select('tsprache', 'cShopStandard', 'Y');

    // Standard-Waehrung
    $oStdWaehrung = Shop::DB()->select('twaehrung', 'cStandard', 'Y');

    // Artikel Default Optionen
    $oArtikelOptions = Artikel::getDefaultOptions();

    // lokalisierter Kuponwert und MBW
    $oKupon->cLocalizedWert = ($oKupon->cWertTyp === 'festpreis') ?
        gibPreisStringLocalized($oKupon->fWert, $oStdWaehrung, 0) :
        $oKupon->fWert . ' %';
    $oKupon->cLocalizedMBW = gibPreisStringLocalized($oKupon->fMindestbestellwert, $oStdWaehrung, 0);

    // kKunde-array aller Kunden
    if ($oKupon->cKunden === '-1') {
        $oKundeDB_arr = Shop::DB()->query("
            SELECT kKunde
                FROM tkunde" .
                ((int)$oKupon->kKundengruppe == -1 ? "" : " WHERE kKundengruppe = " . (int)$oKupon->kKundengruppe),
            2);
    } else {
        $oKundeDB_arr = getCustomers($oKupon->cKunden);
    }

    // Artikel-Nummern
    $oArtikelDB_arr = array();
    $cArtNr_arr     = StringHandler::parseSSK($oKupon->cArtikel);

    if (count($cArtNr_arr) > 0) {
        $oArtikelDB_arr = Shop::DB()->query("
            SELECT kArtikel
                FROM tartikel
                WHERE cArtNr IN (" . implode(',', $cArtNr_arr) . ")",
            2);
    }

    foreach ($oKundeDB_arr as $oKundeDB) {
        $oKunde = new Kunde($oKundeDB->kKunde);

        // Sprache
        $oSprache = Shop::Lang()->getIsoFromLangID($oKunde->kSprache);
        if (!$oSprache) {
            $oSprache = $oStdSprache;
        }

        // Kuponsprache
        $oKuponsprache = Shop::DB()->select('tkuponsprache', array('kKupon', 'cISOSprache'), array($oKupon->kKupon, $oSprache->cISO));

        // Kategorien
        $oKategorie_arr = array();
        if ($oKupon->cKategorien !== '-1') {
            $kKategorie_arr = array_map('intval', StringHandler::parseSSK($oKupon->cKategorien));
            foreach ($kKategorie_arr as $kKategorie) {
                if ($kKategorie > 0) {
                    $oKategorie       = new Kategorie($kKategorie, $oKunde->kSprache, $oKunde->kKundengruppe);
                    $oKategorie->cURL = $oKategorie->cURLFull;
                    $oKategorie_arr[] = $oKategorie;
                }
            }
        }

        // Artikel
        $oArtikel_arr = array();
        foreach ($oArtikelDB_arr as $oArtikelDB) {
            $oArtikel = new Artikel();
            $oArtikel->fuelleArtikel($oArtikelDB->kArtikel, $oArtikelOptions, $oKunde->kKundengruppe, $oKunde->kSprache, true);
            $oArtikel_arr[] = $oArtikel;
        }

        // put all together
        $oKupon->Kategorien      = $oKategorie_arr;
        $oKupon->Artikel         = $oArtikel_arr;
        $oKupon->AngezeigterName = $oKuponsprache->cName;
        $obj                     = new stdClass();
        $obj->tkupon             = $oKupon;
        $obj->tkunde             = $oKunde;
        sendeMail(MAILTEMPLATE_KUPON, $obj);
    }
}
