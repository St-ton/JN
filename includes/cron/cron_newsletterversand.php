<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'smartyinclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kampagne.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'newsletter_inc.php';

/**
 * @param JobQueue $oJobQueue
 * @return bool
 */
function bearbeiteNewsletterversand($oJobQueue)
{
    $oJobQueue->nInArbeit = 1;
    $oNewsletter          = $oJobQueue->holeJobArt();
    $Einstellungen        = Shop::getSettings(array(CONF_NEWSLETTER));
    $mailSmarty           = bereiteNewsletterVor($Einstellungen);
    // Baue Arrays mit kKeys
    $kArtikel_arr      = gibAHKKeys($oNewsletter->cArtikel, true);
    $kHersteller_arr   = gibAHKKeys($oNewsletter->cHersteller);
    $kKategorie_arr    = gibAHKKeys($oNewsletter->cKategorie);
    $kKundengruppe_arr = gibAHKKeys($oNewsletter->cKundengruppe);
    // Baue Kampagnenobjekt, falls vorhanden in der Newslettervorlage
    $oKampagne = new Kampagne(intval($oNewsletter->kKampagne));
    if (count($kKundengruppe_arr) === 0) {
        $oJobQueue->deleteJobInDB();
        // NewsletterQueue löschen
        Shop::DB()->delete('tnewsletterqueue', 'kNewsletter', $oJobQueue->kKey);
        unset($oJobQueue);

        return false;
    }

    // Baue Arrays von Objekten
    $oArtikel_arr   = array();
    $oKategorie_arr = array();

    $cSQL = '';
    if (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0) {
        foreach ($kKundengruppe_arr as $kKundengruppe) {
            $oArtikel_arr[$kKundengruppe]   = gibArtikelObjekte($kArtikel_arr, $oKampagne, $kKundengruppe, $oNewsletter->kSprache);
            $oKategorie_arr[$kKundengruppe] = gibKategorieObjekte($kKategorie_arr, $oKampagne);
        }

        $cSQL = "AND (";
        foreach ($kKundengruppe_arr as $i => $kKundengruppe) {
            if ($i > 0) {
                $cSQL .= " OR tkunde.kKundengruppe=" . intval($kKundengruppe);
            } else {
                $cSQL .= "tkunde.kKundengruppe=" . intval($kKundengruppe);
            }
        }
    }

    if (in_array('0', explode(';', $oNewsletter->cKundengruppe))) {
        if (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0) {
            $cSQL .= " OR tkunde.kKundengruppe is null";
        } else {
            $cSQL .= "tkunde.kKundengruppe is null";
        }
    }
    $cSQL .= ")";

    $oHersteller_arr = gibHerstellerObjekte($kHersteller_arr, $oKampagne, $oNewsletter->kSprache);

    $oNewsletterEmpfaenger_arr = Shop::DB()->query(
        "SELECT tkunde.kKundengruppe, tkunde.kKunde, tsprache.cISO, tnewsletterempfaenger.kNewsletterEmpfaenger, tnewsletterempfaenger.cAnrede, tnewsletterempfaenger.cVorname, tnewsletterempfaenger.cNachname, tnewsletterempfaenger.cEmail, tnewsletterempfaenger.cLoeschCode
            FROM tnewsletterempfaenger
            LEFT JOIN tsprache ON tsprache.kSprache = tnewsletterempfaenger.kSprache
            LEFT JOIN tkunde ON tkunde.kKunde = tnewsletterempfaenger.kKunde
            WHERE tnewsletterempfaenger.kSprache=" . $oNewsletter->kSprache . "
                AND tnewsletterempfaenger.nAktiv=1 " . $cSQL . "
            ORDER BY tnewsletterempfaenger.kKunde
            LIMIT " . $oJobQueue->nLimitN . ", " . $oJobQueue->nLimitM, 2
    );

    if (is_array($oNewsletterEmpfaenger_arr) && count($oNewsletterEmpfaenger_arr) > 0) {
        foreach ($oNewsletterEmpfaenger_arr as $oNewsletterEmpfaenger) {
            unset($oKunde);
            $oNewsletterEmpfaenger->cLoeschURL = Shop::getURL() . '/newsletter.php?lang=' . $oNewsletterEmpfaenger->cISO . '&lc=' . $oNewsletterEmpfaenger->cLoeschCode;
            if ($oNewsletterEmpfaenger->kKunde > 0) {
                require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Kunde.php';

                $oKunde = new Kunde($oNewsletterEmpfaenger->kKunde);
            }

            $kKundengruppeTMP = 0;
            if (intval($oNewsletterEmpfaenger->kKundengruppe) > 0) {
                $kKundengruppeTMP = intval($oNewsletterEmpfaenger->kKundengruppe);
            }

            versendeNewsletter($mailSmarty, $oNewsletter, $Einstellungen, $oNewsletterEmpfaenger, $oArtikel_arr[$kKundengruppeTMP], $oHersteller_arr, $oKategorie_arr[$kKundengruppeTMP], $oKampagne, ((isset($oKunde)) ? $oKunde : null));

            // Newsletterempfaenger updaten
            Shop::DB()->query(
                "UPDATE tnewsletterempfaenger
                    SET dLetzterNewsletter='" . date('Y-m-d H:m:s') . "'
                    WHERE kNewsletterEmpfaenger=" . $oNewsletterEmpfaenger->kNewsletterEmpfaenger, 3
            );
            $oJobQueue->nLimitN += 1;
            $oJobQueue->updateJobInDB();
        }
        $oJobQueue->nInArbeit = 0;
        $oJobQueue->updateJobInDB();
    } else {
        $oJobQueue->deleteJobInDB();
        // NewsletterQueue löschen
        Shop::DB()->query(
            "DELETE FROM tnewsletterqueue
										WHERE kNewsletter=" . $oJobQueue->kKey, 3
        );
        unset($oJobQueue);
    }
}
