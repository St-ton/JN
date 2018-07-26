<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Cron\Jobs;


use Cron\Job;
use Cron\JobInterface;
use Cron\QueueEntry;
use DB\DbInterface;
use DB\ReturnType;
use Psr\Log\LoggerInterface;

/**
 * Class Newsletter
 * @package Cron\Jobs
 */
class Newsletter extends Job
{
    /**
     * @inheritdoc
     */
    public function __construct(DbInterface $db, LoggerInterface $logger)
    {
        parent::__construct($db, $logger);
        if (JOBQUEUE_LIMIT_M_NEWSLETTER > 0) {
            $this->setLimit(JOBQUEUE_LIMIT_M_NEWSLETTER);
        }
    }

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $oNewsletter = $this->getJobData();
        if ($oNewsletter === null) {
            return $this;
        }
        $Einstellungen     = \Shop::getSettings([CONF_NEWSLETTER]);
        $mailSmarty        = bereiteNewsletterVor($Einstellungen);
        $kArtikel_arr      = gibAHKKeys($oNewsletter->cArtikel, true);
        $kHersteller_arr   = gibAHKKeys($oNewsletter->cHersteller);
        $kKategorie_arr    = gibAHKKeys($oNewsletter->cKategorie);
        $kKundengruppe_arr = gibAHKKeys($oNewsletter->cKundengruppe);
        $oKampagne         = new \Kampagne($oNewsletter->kKampagne);
        if (count($kKundengruppe_arr) === 0) {
            $this->setFinished(true);
            $this->db->delete('tnewsletterqueue', 'kNewsletter', $queueEntry->kKey);

            return $this;
        }
        $products   = [];
        $categories = [];
        $cSQL       = '';
        if (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0) {
            foreach ($kKundengruppe_arr as $kKundengruppe) {
                $products[$kKundengruppe]   = gibArtikelObjekte(
                    $kArtikel_arr,
                    $oKampagne,
                    $kKundengruppe,
                    $oNewsletter->kSprache
                );
                $categories[$kKundengruppe] = gibKategorieObjekte($kKategorie_arr, $oKampagne);
            }

            $cSQL = 'AND (';
            foreach ($kKundengruppe_arr as $i => $kKundengruppe) {
                if ($i > 0) {
                    $cSQL .= ' OR tkunde.kKundengruppe = ' . (int)$kKundengruppe;
                } else {
                    $cSQL .= 'tkunde.kKundengruppe = ' . (int)$kKundengruppe;
                }
            }
        }

        if (in_array('0', explode(';', $oNewsletter->cKundengruppe))) {
            if (is_array($kKundengruppe_arr) && count($kKundengruppe_arr) > 0) {
                $cSQL .= ' OR tkunde.kKundengruppe IS NULL';
            } else {
                $cSQL .= 'tkunde.kKundengruppe IS NULL';
            }
        }
        $cSQL .= ')';

        $oHersteller_arr           = gibHerstellerObjekte($kHersteller_arr, $oKampagne, $oNewsletter->kSprache);
        $oNewsletterEmpfaenger_arr = $this->db->query(
            'SELECT tkunde.kKundengruppe, tkunde.kKunde, tsprache.cISO, tnewsletterempfaenger.kNewsletterEmpfaenger, 
            tnewsletterempfaenger.cAnrede, tnewsletterempfaenger.cVorname, tnewsletterempfaenger.cNachname, 
            tnewsletterempfaenger.cEmail, tnewsletterempfaenger.cLoeschCode
                FROM tnewsletterempfaenger
                LEFT JOIN tsprache 
                    ON tsprache.kSprache = tnewsletterempfaenger.kSprache
                LEFT JOIN tkunde 
                    ON tkunde.kKunde = tnewsletterempfaenger.kKunde
                WHERE tnewsletterempfaenger.kSprache = ' . (int)$oNewsletter->kSprache . '
                    AND tnewsletterempfaenger.nAktiv = 1 ' . $cSQL . '
                ORDER BY tnewsletterempfaenger.kKunde
                LIMIT ' . $queueEntry->nLimitN . ', ' . $queueEntry->nLimitM,
            ReturnType::ARRAY_OF_OBJECTS
        );

        if (is_array($oNewsletterEmpfaenger_arr) && count($oNewsletterEmpfaenger_arr) > 0) {
            $shopURL = \Shop::getURL();
            foreach ($oNewsletterEmpfaenger_arr as $oNewsletterEmpfaenger) {
                unset($oKunde);
                $oNewsletterEmpfaenger->cLoeschURL = $shopURL . '/newsletter.php?lang=' .
                    $oNewsletterEmpfaenger->cISO . '&lc=' . $oNewsletterEmpfaenger->cLoeschCode;
                if ($oNewsletterEmpfaenger->kKunde > 0) {
                    $oKunde = new \Kunde($oNewsletterEmpfaenger->kKunde);
                }
                $kKundengruppeTMP = (int)$oNewsletterEmpfaenger->kKundengruppe > 0
                    ? (int)$oNewsletterEmpfaenger->kKundengruppe
                    : 0;

                versendeNewsletter(
                    $mailSmarty,
                    $oNewsletter,
                    $Einstellungen,
                    $oNewsletterEmpfaenger,
                    $products[$kKundengruppeTMP],
                    $oHersteller_arr,
                    $categories[$kKundengruppeTMP],
                    $oKampagne,
                    $oKunde ?? null
                );
                // Newsletterempfaenger updaten
                $this->db->query(
                    "UPDATE tnewsletterempfaenger
                        SET dLetzterNewsletter = '" . date('Y-m-d H:m:s') . "'
                        WHERE kNewsletterEmpfaenger = " . (int)$oNewsletterEmpfaenger->kNewsletterEmpfaenger,
                    ReturnType::DEFAULT
                );
                ++$queueEntry->nLimitN;
            }
            $this->setFinished(false);
        } else {
            $this->setFinished(true);
            $this->db->delete('tnewsletterqueue', 'kNewsletter', $queueEntry->kKey);
        }

        return $this;
    }
}
