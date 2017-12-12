<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';

$oAccount->permission('PLUGIN_ADMIN_VIEW', true, true);
/** @global JTLSmarty $smarty */
$cHinweis      = '';
$cFehler       = '';
$step          = 'uebersicht';

setzeSprache();

$pluginID = (isset($_GET['plugin_id'])) ? $_GET['plugin_id'] : 's360_amazon_lpa_shop4';
$pp       = null;
if (!empty($pluginID)) {
    $pp = new PremiumPlugin($pluginID);
    if ($pluginID === 's360_amazon_lpa_shop4') {
        $pp->setLongDescription('Schnell, einfach und sicher.',
            '"Login und Bezahlen mit Amazon" ist die schnelle, einfache und sichere Art, Shop-Besucher zu Kunden zu machen. 
        Ermöglichen Sie Millionen von Amazon-Kunden, sich in Ihrem Shop über "Login und Bezahlen mit Amazon" in ihr Amazon-Kundenkonto einzuloggen und mit den dort hinterlegten Zahlungs- und Versandinformationen in Ihrem Shop zu bezahlen. 
        Jeder Kunde, der ein Amazon-Kundenkonto besitzt, kann "Login und Bezahlen mit Amazon" als Zahlungsart in Ihrem Shop auswählen.');
        $pp->setShortDescription('Zertifiziertes Plugin für JTL-Shop 4',
            'Für JTL-Shop 4 steht Ihnen "Login und Bezahlen mit Amazon" als zertifiziertes Plugin direkt im Backend zur Verfügung.');
        $pp->setTitle('Amazon Payments Login & Pay (JTL Shop 4)');

        $pp->setAuthor('Solution 360 GmbH');

        $pp->addButton('Jetzt registrieren', 'https://payments.amazon.de/?ld=SPEXDEAPA-JTL-CP-DP-2016-07', 'btn btn-primary', 'sign-in')
           ->addButton('Dokumentation', 'https://shop.solution360.de/downloads/dokus/Plugin_Doku_AmazonLogin&Pay-Shop4.pdf', 'btn btn-default', null, true);

        $pp->addAdvantage('Neukundengewinnung und verbessertes Einkaufserlebnis - Chance auf höhere Konversion und mehr Umsatz Online-Shop durch vereinfachten Bezahlprozess. Käufer werden zu Ihren Kunden und Sie können Ihre Produkte direkt an sie vermarkten.')
           ->addAdvantage('Desktop-, Tablet- und Smartphone-optimierte Buttons und Widgets - Erzielen Sie Verkäufe, die Ihnen ohne Mobiloptimierung entgehen würden.')
           ->addAdvantage('Zahlungsvorgang als Widget in Ihrem Shop - keine Weiterleitung auf eine externe Website')
           ->addAdvantage('Reine Zahlungsabwicklung - keine Weitergabe von Artikel- oder Warenkorbdaten an Amazon')
           ->addAdvantage('Schutz vor Zahlungsausfall und Betrugsversuchen')
           ->addAdvantage('Kostensenkung durch transaktionsbasiertes Preismodell ohne Grundgebühren, Vorauszahlungen o.Ä.');

        $pp->addHowTo('Registrieren Sie sich bei Amazon Payments unter <a title="Amazon Payments" href="https://payments.amazon.de/?ld=SPEXDEAPA-JTL-CP-DP-2016-07" target="_blank"><i class="fa fa-external-link"></i> https://payments.amazon.de/</a>')
           ->addHowTo('Aktivieren Sie das Amazon Payments Plugin in Ihrem JTL-Shop 4')
           ->addHowTo('Konfigurieren Sie das Amazon Payments Plugin mit Hilfe der Dokumentation von Solution 360. Diese finden Sie unter diesem <a title="Dokumentation" href="https://shop.solution360.de/downloads/dokus/Plugin_Doku_AmazonLogin&Pay-Shop4.pdf" target="_blank"><i class="fa fa-external-link"></i> Link</a>.')
           ->addHowTo('Fertig!');

        $ss          = new stdClass();
        $ss->preview = 'https://bilder.jtl-software.de/erweiterungen/1165396.png';
        $ss->full    = 'https://bilder.jtl-software.de/erweiterungen/1165396.png';
        $pp->addScreenShot($ss);

        $pp->setDownloadLink('https://shop.solution360.de/Login-und-Bezahlen-mit-Amazon-JTL-Shop4-Plugin');

        $sp                        = new stdClass();
        $sp->kServicePartner       = 519;
        $sp->marketPlaceURL        = 'https://www.jtl-software.de/Servicepartner-Detailansicht?id=' . $sp->kServicePartner;
        $sp->oZertifizierungen_arr = array(
            'https://bilder.jtl-software.de/zertifikat/jtl_premium_sp_280.png',
            'https://bilder.jtl-software.de/zertifikat/jtl_cert_badge_1_280.png',
            'https://bilder.jtl-software.de/zertifikat/jtl_cert_badge_6_280.png',
            'https://bilder.jtl-software.de/zertifikat/jtl_cert_badge_7_280.png',
            'https://bilder.jtl-software.de/zertifikat/jtl_cert_badge_8_280.png',
        );
        $sp->cLogoPfad             = 'https://bilder.jtl-software.de/splogos/kServicepartner_519.png';
        $sp->cFirma                = 'Solution 360 GmbH';
        $sp->cPLZ                  = '10179';
        $sp->cOrt                  = 'Berlin';
        $sp->cStrasse              = 'Engeldamm 20';
        $sp->cWWW                  = 'http://www.solution360.de';
        $sp->cMail                 = 'mail@solution360.de';
        $sp->cAdresszusatz         = '';
        $sp->cLandName             = 'Deutschland';

        $pp->setServicePartner($sp);

        $pp->addBadge('AmazonPayments_PartnerLogos_Black_Premier_Partner.png', true);
    } elseif ($pluginID === 'agws_ts_features') {
        $pp->setLongDescription('Zeigen Sie, dass Ihre Kunden Sie lieben!',
            'Die einzigartige Trustbadge Technologie ermöglicht es Ihnen automatisiert Shopbewertungen und Produktbewertungen zu sammeln und direkt im Shop konversionssteigernd anzuzeigen. 
            So zeigen Sie Ihren Besuchern, dass Sie vertrauenswürdig sind und überzeugen Sie in Ihrem Shop beruhigt einkaufen zu können. ');
        $pp->setShortDescription('Zertifiziertes Plugin für JTL-Shop 4',
            'Für JTL-Shop 4 steht Ihnen "Trustbadge Reviews" als zertifiziertes Plugin direkt im Backend zur Verfügung.');
        $pp->setTitle('Trustbadge Reviews (JTL Shop 4)');

        $pp->setAuthor('Trusted Shops GmbH');

        $pp->addButton('Jetzt registrieren', 'https://business.trustedshops.de/produkte/bewertungen/?utm_source=jtl&utm_medium=software-app&utm_content=marketing-page&utm_campaign=jtl-app', 'btn btn-primary', 'sign-in')
           ->addButton('Dokumentation', 'http://www.trustedshops.de/shopbetreiber/integration/shopsoftware-integration/jtl/?shop_id=&variant=&yOffset=', 'btn btn-default', null, true);

        $pp->addAdvantage('Sammeln Sie Shop- und Produktbewertungen automatisch von echten Kunden')
           ->addAdvantage('Steigern Sie Ihre Reichweite durch ein besseres Suchmaschinenranking mit Ihrer individuellen Profilseite')
           ->addAdvantage('Erleichtern Sie Ihren Kunden die Kaufentscheidung')
           ->addAdvantage('Erhöhen Sie Ihren Umsatz')
           ->addAdvantage('Zeigen Sie Ihre Vertrauenswürdigkeit')
           ->addAdvantage('Passen Sie das Trustbadge an das Design Ihres Shops an')
           ->addAdvantage('100% Mobile ready')
           ->addAdvantage('Upgrade jederzeit möglich');

        $pp->addHowTo('Registrieren Sie sich für einen kostenlosen Account mit einem Klick auf den unteren Button')
           ->addHowTo('Bestätigen Sie die Double-Opt-In eMail')
           ->addHowTo('Aktivieren Sie das Trustbadge Reviews Plugin in Ihrem JTL-Shop 4 und fügen Sie Ihre TS-ID ein')
           ->addHowTo('Konfigurieren Sie, falls gewünscht, Ihr Trustbadge')
           ->addHowTo('Super, schon fertig!');

        $baseURL     = Shop::getURL() . '/' . PFAD_ADMIN . PFAD_GFX . 'PremiumPlugins/';
        $ss          = new stdClass();
        $ss->preview = $baseURL . 'agws_ts_features_01.jpg';
        $ss->full    = $baseURL . 'agws_ts_features_01.jpg';
        $pp->addScreenShot($ss);
        $ss          = new stdClass();
        $ss->preview = $baseURL . 'agws_ts_features_02.png';
        $ss->full    = $baseURL . 'agws_ts_features_02.png';
        $pp->addScreenShot($ss);
        $ss          = new stdClass();
        $ss->preview = $baseURL . 'agws_ts_features_03.png';
        $ss->full    = $baseURL . 'agws_ts_features_03.png';
        $pp->addScreenShot($ss);

        $pp->setDownloadLink('http://www.trustedshops.de/shopbetreiber/integration/shopsoftware-integration/jtl/?shop_id=&variant=&yOffset=');

        $sp                        = new stdClass();
        $sp->kServicePartner       = 0;
        $sp->marketPlaceURL        = null;
        $sp->oZertifizierungen_arr = array();
        $sp->cLogoPfad             = $baseURL . 'agws_ts_features_logo.png';;
        $sp->cFirma                = 'Trusted Shops GmbH';
        $sp->cPLZ                  = '50823';
        $sp->cOrt                  = utf8_decode('Köln');
        $sp->cStrasse              = utf8_decode('Subbelrather Straße 15c');
        $sp->cWWW                  = 'https://business.trustedshops.de/produkte/bewertungen/';
        $sp->cMail                 = 'welcome@trustbadge.com';
        $sp->cAdresszusatz         = '';
        $sp->cLandName             = 'Deutschland';

        $pp->setServicePartner($sp);

        $pp->setHeaderColor('#FFDC0F');
    } else {
        $pp->setPluginID(null);
    }
}

$smarty->assign('pp', $pp)
       ->display('premiumplugin.tpl');
