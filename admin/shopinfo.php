<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

@ini_set('max_execution_time', 0);

$oAccount->permission('EXPORT_SHOPINFO_VIEW', true, true);

$arMapping       = [];
$arKategorien    = [];
$arEinstellungen = Shop::getSettings([CONF_GLOBAL]);

if (isset($_POST['post']) && (int)$_POST['post'] === 1) {
    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_updateInterval'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_updateInterval'] . "' WHERE cName = 'shopInfo_updateInterval'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_updateInterval')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_updateInterval'] . "' WHERE cName = 'shopInfo_updateInterval'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_logoURL'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_logoURL'] . "' WHERE cName = 'shopInfo_logoURL'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_logoURL')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_logoURL'] . "' WHERE cName = 'shopInfo_logoURL'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_publicMail'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_publicMail'] . "' WHERE cName = 'shopInfo_publicMail'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_publicMail')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_publicMail'] . "' WHERE cName = 'shopInfo_publicMail'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_privateMail'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_privateMail'] . "' WHERE cName = 'shopInfo_privateMail'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_privateMail')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_privateMail'] . "' WHERE cName = 'shopInfo_privateMail'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_orderPhone'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_orderPhone'] . "' WHERE cName = 'shopInfo_orderPhone'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_orderPhone')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_orderPhone'] . "' WHERE cName = 'shopInfo_orderPhone'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_orderFax'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_orderFax'] . "' WHERE cName = 'shopInfo_orderFax'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_orderFax')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_orderFax'] . "' WHERE cName = 'shopInfo_orderFax'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_hotlineNumber'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_hotlineNumber'] . "' WHERE cName = 'shopInfo_hotlineNumber'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_hotlineNumber')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_hotlineNumber'] . "' WHERE cName = 'shopInfo_hotlineNumber'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_costPerMinute'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_costPerMinute'] . "' WHERE cName = 'shopInfo_costPerMinute'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_costPerMinute')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_costPerMinute'] . "' WHERE cName = 'shopInfo_costPerMinute'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_costPerCall'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_costPerCall'] . "' WHERE cName = 'shopInfo_costPerCall'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_costPerCall')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_costPerCall'] . "' WHERE cName = 'shopInfo_costPerCall'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_installment'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_installment'] . "' WHERE cName = 'shopInfo_installment'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_installment')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_installment'] . "' WHERE cName = 'shopInfo_installment'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_repairservice'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_repairservice'] . "' WHERE cName = 'shopInfo_repairservice'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_repairservice')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_repairservice'] . "' WHERE cName = 'shopInfo_repairservice'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_giftservice'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_giftservice'] . "' WHERE cName = 'shopInfo_giftservice'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_giftservice')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_giftservice'] . "' WHERE cName = 'shopInfo_giftservice'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_orderTracking'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_orderTracking'] . "' WHERE cName = 'shopInfo_orderTracking'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_orderTracking')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_orderTracking'] . "' WHERE cName = 'shopInfo_orderTracking'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_deliverTracking'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_deliverTracking'] . "' WHERE cName = 'shopInfo_deliverTracking'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_deliverTracking')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_deliverTracking'] . "' WHERE cName = 'shopInfo_deliverTracking'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_installationAssistance'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_installationAssistance'] . "' WHERE cName = 'shopInfo_installationAssistance'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_installationAssistance')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_installationAssistance'] . "' WHERE cName = 'shopInfo_installationAssistance'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_certificationItems'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_certificationItems'] . "' WHERE cName = 'shopInfo_certificationItems'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_certificationItems')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_certificationItems'] . "' WHERE cName = 'shopInfo_certificationItems'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_trusteesItems'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_trusteesItems'] . "' WHERE cName = 'shopInfo_trusteesItems'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_trusteesItems')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_trusteesItems'] . "' WHERE cName = 'shopInfo_trusteesItems'";
        Shop::DB()->query($strSQL, 4);
    }

    $oTmpConf = Shop::DB()->query("SELECT * FROM teinstellungen WHERE cName = 'shopInfo_payItems'", 1);
    if ($oTmpConf) {
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_payItems'] . "' WHERE cName = 'shopInfo_payItems'";
        Shop::DB()->query($strSQL, 4);
    } else {
        Shop::DB()->exQuery("INSERT INTO teinstellungen (kEinstellungenSektion,cName) VALUES (103,'shopInfo_payItems')");
        $strSQL = "UPDATE teinstellungen SET cWert = '" . $_POST['shopInfo_payItems'] . "' WHERE cName = 'shopInfo_payItems'";
        Shop::DB()->query($strSQL, 4);
    }

    $strSQL = "DELETE FROM tkategoriemapping";
    Shop::DB()->query($strSQL, 4);
    foreach ($_POST['Mapping'] as $val) {
        $ar              = preg_split('/_/', $val);
        $tmp             = $ar[0];
        $arMapping[$tmp] = $ar[1];
        $strSQL          = "INSERT INTO tkategoriemapping SET kKategorie = " . (int)$ar[0] . ", cName = '" . $ar[1] . "'";
        Shop::DB()->query($strSQL, 4);
    }
    Shop::Cache()->flushTags([CACHING_GROUP_OPTION]);

    $strSQL       = "SELECT kKategorie, cName FROM tkategorie WHERE kOberkategorie <= 0 ";
    $arKategorien = Shop::DB()->query($strSQL, 2);
}

//daily|mon|tue|wed|thu|fri|sat|sun
$updateInterval = (!empty($_POST['shopInfo_updateInterval'])) ? $_POST['shopInfo_updateInterval'] : 'daily';
// 90x40px
$logoURL = (!empty($_POST['shopInfo_logoURL'])) ? $_POST['shopInfo_logoURL'] : '';
// Eine E-Mail-Adresse, die vom elektronischen Markt angezeigt werden soll
//  und Nutzern dazu dient, Kontakt mit dem Shop aufzunehmen.
$publicMail = (!empty($_POST['shopInfo_publicMail'])) ? $_POST['shopInfo_publicMail'] : '';
// Eine E-Mail-Adresse, die ausschliesslich der Kommunikation zwischen
//  Marktbetreiber und Shop dienen und nicht angezeigt werden soll.
$privateMail = (!empty($_POST['shopInfo_privateMail'])) ? $_POST['shopInfo_privateMail'] : '';
// Angaben zu telefonischen Bestellungen.
$orderPhone = (!empty($_POST['shopInfo_orderPhone'])) ? $_POST['shopInfo_orderPhone'] : '';
// Angaben zu Bestellungen per Fax
$orderFax      = (!empty($_POST['shopInfo_orderFax'])) ? $_POST['shopInfo_orderFax'] : '';
$hotlineNumber = (!empty($_POST['shopInfo_hotlineNumber'])) ? $_POST['shopInfo_hotlineNumber'] : '';
// Die Kosten pro Minute. Das Fehlen dieses und des folgenden Elements
//  CostPerCall bedeutet, dass normale, i. A. entfernungsabhaengige Gebuehren
//  anfallen (d. h. es handelt sich z. B. nicht um eine 0180- oder
//  0190-Nummer). Attribut currency: Waehrungscode nach ISO 4217:1995, bspw.
//  EUR. Es sind vier Nachkommastellen erlaubt, z. B. 0.0149 fuer 1,49 Cent
//  pro Minute.
$costPerMinute = (!empty($_POST['shopInfo_costPerMinute'])) ? $_POST['shopInfo_costPerMinute'] : '';
$costPerCall   = (!empty($_POST['shopInfo_costPerCall'])) ? $_POST['shopInfo_costPerCall'] : '';
// Moeglichkeit der Ratenzahlung
$installment = '';
if (isset($_POST['shopInfo_installment']) && $_POST['shopInfo_installment'] === 'Y') {
    $installment = '<Installment />';
}
// Reparaturdienstleistungen (mit/ohne Zusatzkosten)
$repairservice = '';
if (isset($_POST['shopInfo_repairservice']) && $_POST['shopInfo_repairservice'] === 'Y') {
    $repairservice = '<RepairService surcharge="no" />';
} elseif (isset($_POST['shopInfo_repairservice']) && $_POST['shopInfo_repairservice'] === 'Y+') {
    $repairservice = '<RepairService surcharge="yes" />';
}
// Geschenkservice (mit/ohne Zusatzkosten)
$giftservice = '';
if (isset($_POST['shopInfo_giftservice']) && $_POST['shopInfo_giftservice'] === 'Y') {
    $giftservice = '<GiftService surcharge="no" />';
} elseif (isset($_POST['shopInfo_giftservice']) && $_POST['shopInfo_giftservice'] === 'Y+') {
    $giftservice = '<GiftService surcharge="yes" />';
}
// Moeglichkeit der Bestellverfolgung
$orderTracking = '';
if (isset($_POST['shopInfo_orderTracking']) && $_POST['shopInfo_orderTracking'] === 'Y') {
    $orderTracking = '<OrderTracking />';
}
//Moeglichkeit der Lieferverfolgung
$deliverTracking = '';
if (isset($_POST['shopInfo_deliverTracking']) && $_POST['shopInfo_deliverTracking'] === 'Y') {
    $deliverTracking = '<DeliverTracking />';
}
// Unterstuetzung bei der Inbetriebnahme/Installation des Produkts (mit/ohne
//  Zusatzkosten)
$installationAssistance = '';
if (isset($_POST['shopInfo_installationAssistance']) && $_POST['shopInfo_installationAssistance'] === 'Y') {
    $installationAssistance = '<InstallationAssistance surcharge="no" />';
} elseif (isset($_POST['shopInfo_installationAssistance']) && $_POST['shopInfo_installationAssistance'] === 'Y+') {
    $installationAssistance = '<InstallationAssistance surcharge="yes" />';
}
/*  Angabe von Zertifizierungen
    * BoniCert
    * Bonitrus
    * CHIP Online
    * Euro-Label
    * Eurocard Safe'n Easy
    * EuroSHOPPING24
    * faircommerce
    * gütezeichen.at
    * MSP-Info
    * s@fer shopping
    * Shoplupe
    * Trusted Shops
    * TrustLogo */

// certificationItems
$certificationItems = '';
if (isset($_POST['shopInfo_certificationItems']) && strlen($_POST['shopInfo_certificationItems']) > 0) {
    $certificationItems .= "<Certifications>\n";
    $arItems = explode(',', $_POST['shopInfo_certificationItems']);
    foreach ($arItems as $var) {
        $var = trim($var);
        $certificationItems .= "\t\t<Item>$var</Item>\n";
    }

    $certificationItems .= "\t</Certifications>";
}

/*  * iclear
    * iloxx SAFETRADE
    * S-ITT */
$trusteesItems = '';
if (isset($_POST['shopInfo_trusteesItems']) && strlen($_POST['shopInfo_trusteesItems']) > 0) {
    $trusteesItems .= "<Trustees>\n";
    $arItems = explode(',', $_POST['shopInfo_trusteesItems']);
    foreach ($arItems as $var) {
        $var = trim($var);
        $trusteesItems .= "\t\t<Item>$var</Item>\n";
    }

    $trusteesItems .= "\t</Trustees>";
}

// paymentItems
$paymentItems = '';
if (isset($_POST['shopInfo_payItems']) && strlen($_POST['shopInfo_payItems']) > 0) {
    $paymentItems .= "<Payment>\n";
    $arItems = explode(',', $_POST['shopInfo_payItems']);
    foreach ($arItems as $var) {
        $var = trim($var);
        $paymentItems .= "\t<Item>\n\t<Name>$var</Name>\n\t</Item>\n";
    }

    $paymentItems .= "\t</Payment>";

    /*
    ...Surcharge? (currency?) 	Zusaetzliche Gebuehren. Ein negativer Wert wird als Praemie interpretiert. Attribut currency: Waehrungscode nach ISO 4217:1995.
    ...MaxSurcharge? (currency?) 	Hoechstgrenze fuer zusaetzliche Gebuehren. Ein negativer Wert wird als Praemie interpretiert. Attribut currency: Waehrungscode nach ISO 4217:1995.
    ...RelativeSurcharge? 	Zusaetzliche relative Gebuehren in Prozent der Bestellsumme. Ein negativer Wert wird als Praemie interpretiert.
    */
}

$lang            = 'de';
$currency        = 'EUR';
$ShopName        = StringHandler::htmlspecialchars($arEinstellungen['global']['global_shopname']);
$ShopDescription = '';
if (isset($arEinstellungen['global']['global_meta_description']) && strlen($arEinstellungen['global']['global_meta_description']) > 0) {
    $ShopDescription = "<Self-Description>" . StringHandler::htmlspecialchars($arEinstellungen['global']['global_meta_description']) . "</Self-Description>";
}
$ShopURL     = Shop::getURL();
$headerCSV   = "Artikelnummer;Hersteller;Bezeichnung;Preis;Lieferbarkeit;Sonderangebot;URL;Bildlink;Beschreibung;Versandkosten;EAN;Kurzbeschreibung;Kategorie;Einheit";
$headerCount = 14;
$mappingCols = "\t\t\t\t\t\t<Mapping column=\"1\" columnName=\"Artikelnummer\" type=\"privateid\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"2\" columnName=\"Hersteller\" type=\"brand\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"3\" columnName=\"Bezeichnung\" type=\"name\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"4\" columnName=\"Preis\" type=\"price\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"5\" columnName=\"Lieferbarkeit\" type=\"deliverable\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"6\" columnName=\"Sonderangebot\" type=\"specialdiscount\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"7\" columnName=\"URL\" type=\"url\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"8\" columnName=\"Bildlink\" type=\"pictureurl\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"9\" columnName=\"Beschreibung\" type=\"longdescription\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"10\" columnName=\"Versandkosten\" type=\"deliverydetails\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"11\" columnName=\"EAN\" type=\"ean\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"12\" columnName=\"Kurzbeschreibung\" type=\"shortdescription\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"13\" columnName=\"Kategorie\" type=\"type\"/>\n" .
    "\t\t\t\t\t\t<Mapping column=\"14\" columnName=\"Einheit\" type=\"unit\"/>\n";
$shopAddress = '';
$saleAddress = '';
$hotline     = '';
if (strlen($hotlineNumber) > 0) {
    $hotline = "\t\t<Hotline>\n";
    $hotline .= "\t\t\t<Number>$hotlineNumber</Number>\n";
    if (strlen($costPerMinute) > 0) {
        $hotline .= "\t\t\t<CostPerMinute>$costPerMinute</CostPerMinute>\n";
    }
    if (strlen($costPerCall) > 0) {
        $hotline .= "\t\t\t<CostPerCall>$costPerCall</CostPerCall>\n";
    }
    $hotline .= "\t\t</Hotline>\n";
}
$categories = '';
foreach ($arKategorien as $objKategorie) {
    $categories .=
        "<Item>\n\t\t\t<Name>" . StringHandler::htmlspecialchars($objKategorie->cName) . "</Name>\n" .
        //"\t\t\t<ProductCount>23</ProductCount>\n".
        "\t\t\t<Mapping>" . StringHandler::htmlspecialchars($arMapping[$objKategorie->kKategorie]) . "</Mapping>\n" .
        "</Item>";
}

$flatRate   = ''; // Versandkostenpauschale. Attribut currency: Waehrungscode nach ISO 4217:1995.
$upperBound = ''; // Bestellwert, ab dem keine Versandkosten mehr anfallen. Attribut currency: Waehrungscode nach ISO 4217:1995.
$SSL        = '';

if ($arEinstellungen['global']['kaufabwicklung_ssl_nutzen']) {
    $SSL = '<SSL />';
}
$urlAGB = '';
// orderPhone
if (strlen($orderPhone) > 0) {
    $orderPhone = "
		<OrderPhone>
			<Number>$orderPhone</Number>
		</OrderPhone>
	";
}
// orderFax
if (strlen($orderFax) > 0) {
    $orderFax = "
		<OrderFax>
			<Number>$orderFax</Number>
		</OrderFax>
	";
}
// logoURL
if (strlen($logoURL) > 0) {
    $logoURL = "<Logo>" . $logoURL . "</Logo>";
}
// publicMail
if (strlen($publicMail) > 0) {
    $publicMail = "<PublicMailAddress>$publicMail</PublicMailAddress>";
}
// privateMail
if (strlen($privateMail) > 0) {
    $privateMail = "<PrivateMailAddress>$privateMail</PrivateMailAddress>";
}

$XML = <<<EOF
        <Common>
            <Version>1.1</Version>
            <Language>de</Language>
            <Currency>EUR</Currency>
        </Common>
        <Name>$ShopName</Name>
        <Url>$ShopURL</Url>
        <Requests>
            <OnlineRequest method="GET">
                <Processor>$ShopURL/navi.php</Processor>
                <ParamQuickSearch>suchausdruck</ParamQuickSearch>
            </OnlineRequest>
            <OfflineRequest>
                <UpdateMethods>
                    <Email/>
                    <Ftp/>
                    <Manual/>
                    <DirectDownload day="daily" />
                </UpdateMethods>
                <Format>
                    <Tabular>
                        <CSV>
                            <Url>$ShopURL/export/elmar.csv</Url>
                            <Header columns="$headerCount">$headerCSV</Header>
                            <SpecialCharacters delimiter=";" escaped="\" quoted="'"/>
                        </CSV>
                        <Mappings>
                            $mappingCols
                        </Mappings>
                    </Tabular>
                </Format>
            </OfflineRequest>
        </Requests>
        $logoURL
        <Contact>
            $publicMail
            $privateMail
			$orderPhone
            $orderFax
            $hotline
        </Contact>
        <Categories>
            $categories
        </Categories>
            $paymentItems
        <Technology>
            <SSL/>
            <SET/>
            <Search/>
        </Technology>
        $certificationItems
        $trusteesItems
        $ShopDescription
EOF;

$fileShopFeed = basename(FILE_SHOP_FEED);
$bWrite       = is_writable(PFAD_ROOT . $fileShopFeed);

if ($bWrite) {
    if (isset($_REQUEST['update']) && (int)$_REQUEST['update'] === 1) {
        $file = fopen(PFAD_ROOT . $fileShopFeed, "w+");

        fwrite(
            $file, '<?xml version="1.0" encoding="' . JTL_CHARSET . '"?>
                        <osp:Shop xmlns:osp="http://elektronischer-markt.de/schema"
                                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                                xsi:schemaLocation="http://elektronischer-markt.de/schema
                                                    http://kuhlins.de/elmar/schema/shop.xsd">'
        );
        fwrite($file, $XML);
        fwrite($file, "</osp:Shop>");
        fclose($file);
    }

    if (isset($_REQUEST['update']) && (int)$_REQUEST['update'] === 1) {
        header('location: shopinfoexport.php');
    } else {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/xml');
        header('Content-Disposition: attachment; filename="' . $fileShopFeed . '"');
        readfile(PFAD_ROOT . $fileShopFeed);
    }
} else {
    header('location: shopinfoexport.php?bWrite=0');
}
