<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Request;
use JTL\Kampagne;
use JTL\Shop;
use JTL\Session\Frontend;

require_once __DIR__ . '/globalinclude.php';

$session = Frontend::getInstance();

// kK   = kKampagne
// kN   = kNewsletter
// kNE  = kNewsletterEmpfaenger
if (Request::verifyGPCDataInt('kK') > 0
    && Request::verifyGPCDataInt('kN') > 0
    && Request::verifyGPCDataInt('kNE') > 0
) {
    $kKampagne             = Request::verifyGPCDataInt('kK');
    $kNewsletter           = Request::verifyGPCDataInt('kN');
    $kNewsletterEmpfaenger = Request::verifyGPCDataInt('kNE');
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
        $oNewsletterTrack->dErstellt             = 'NOW()';

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
