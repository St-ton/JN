<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/globalinclude.php';

$session = \Session\Session::getInstance();

// kK   = kKampagne
// kN   = kNewsletter
// kNE  = kNewsletterEmpfaenger
if (RequestHelper::verifyGPCDataInt('kK') > 0 && RequestHelper::verifyGPCDataInt('kN') > 0 && RequestHelper::verifyGPCDataInt('kNE') > 0) {
    $kKampagne             = RequestHelper::verifyGPCDataInt('kK');
    $kNewsletter           = RequestHelper::verifyGPCDataInt('kN');
    $kNewsletterEmpfaenger = RequestHelper::verifyGPCDataInt('kNE');
    // Prüfe ob der Newsletter vom Newsletterempfänger bereits geöffnet wurde.
    $oNewsletterTrackTMP = Shop::Container()->getDB()->select(
        'tnewslettertrack',
        'kKampagne',
        $kKampagne,
        'kNewsletter',
        $kNewsletter,
        'kNewsletterEmpfaenger',
        $kNewsletterEmpfaenger,
        false,
        'kNewsletterTrack'
    );
    if (!isset($oNewsletterTrackTMP->kNewsletterTrack)) {
        $oNewsletterTrack                        = new stdClass();
        $oNewsletterTrack->kKampagne             = $kKampagne;
        $oNewsletterTrack->kNewsletter           = $kNewsletter;
        $oNewsletterTrack->kNewsletterEmpfaenger = $kNewsletterEmpfaenger;
        $oNewsletterTrack->dErstellt             = 'now()';

        $kNewsletterTrack = Shop::Container()->getDB()->insert('tnewslettertrack', $oNewsletterTrack);

        if ($kNewsletterTrack > 0) {
            $oKampagne = new Kampagne($kKampagne);
            // Kampagnenbesucher in die Session
            $_SESSION['Kampagnenbesucher'] = $oKampagne;

            Kampagne::setCampaignAction(KAMPAGNE_DEF_NEWSLETTER, $kNewsletterTrack, 1);
        }
    }
}

echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
