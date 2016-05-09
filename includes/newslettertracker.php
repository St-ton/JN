<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/globalinclude.php';

$session = Session::getInstance();

// kK	= kKampagne
// kN	= kNewsletter
// kNE 	= kNewsletterEmpfaenger
if (verifyGPCDataInteger('kK') > 0 && verifyGPCDataInteger('kN') > 0 && verifyGPCDataInteger('kNE') > 0) {
    $kKampagne             = verifyGPCDataInteger('kK');
    $kNewsletter           = verifyGPCDataInteger('kN');
    $kNewsletterEmpfaenger = verifyGPCDataInteger('kNE');
    // Prüfe ob der Newsletter vom Newsletterempfänger bereits geöffnet wurde.
    $oNewsletterTrackTMP = Shop::DB()->query(
        "SELECT kNewsletterTrack
            FROM tnewslettertrack
            WHERE kKampagne = " . $kKampagne . "
                AND kNewsletter = " . $kNewsletter . "
                AND kNewsletterEmpfaenger = " . $kNewsletterEmpfaenger, 1
    );

    if (!isset($oNewsletterTrackTMP->kNewsletterTrack)) {
        $oNewsletterTrack                        = new stdClass();
        $oNewsletterTrack->kKampagne             = $kKampagne;
        $oNewsletterTrack->kNewsletter           = $kNewsletter;
        $oNewsletterTrack->kNewsletterEmpfaenger = $kNewsletterEmpfaenger;
        $oNewsletterTrack->dErstellt             = 'now()';

        $kNewsletterTrack = Shop::DB()->insert('tnewslettertrack', $oNewsletterTrack);

        if ($kNewsletterTrack > 0) {
            $oKampagne = new Kampagne($kKampagne);
            // Kampagnenbesucher in die Session
            $_SESSION['Kampagnenbesucher'] = new stdClass();
            $_SESSION['Kampagnenbesucher'] = $oKampagne;

            setzeKampagnenVorgang(KAMPAGNE_DEF_NEWSLETTER, $kNewsletterTrack, 1);
        }
    }
}

echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
