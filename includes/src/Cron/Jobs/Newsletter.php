<?php declare(strict_types=1);
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
        if (\JOBQUEUE_LIMIT_M_NEWSLETTER > 0) {
            $this->setLimit((int)\JOBQUEUE_LIMIT_M_NEWSLETTER);
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
        $conf            = \Shop::getSettings([\CONF_NEWSLETTER]);
        $smarty          = \bereiteNewsletterVor($conf);
        $productIDs      = \gibAHKKeys($oNewsletter->cArtikel, true);
        $manufacturerIDs = \gibAHKKeys($oNewsletter->cHersteller);
        $categoryIDs     = \gibAHKKeys($oNewsletter->cKategorie);
        $customerGroups  = \gibAHKKeys($oNewsletter->cKundengruppe);
        $campaign        = new \Kampagne($oNewsletter->kKampagne);
        if (\count($customerGroups) === 0) {
            $this->setFinished(true);
            $this->db->delete('tnewsletterqueue', 'kNewsletter', $queueEntry->kKey);

            return $this;
        }
        $products   = [];
        $categories = [];
        $cSQL       = '';
        if (\is_array($customerGroups) && \count($customerGroups) > 0) {
            foreach ($customerGroups as $kKundengruppe) {
                $products[$kKundengruppe]   = \gibArtikelObjekte(
                    $productIDs,
                    $campaign,
                    $kKundengruppe,
                    $oNewsletter->kSprache
                );
                $categories[$kKundengruppe] = \gibKategorieObjekte($categoryIDs, $campaign);
            }

            $cSQL = 'AND (';
            foreach ($customerGroups as $i => $kKundengruppe) {
                if ($i > 0) {
                    $cSQL .= ' OR tkunde.kKundengruppe = ' . (int)$kKundengruppe;
                } else {
                    $cSQL .= 'tkunde.kKundengruppe = ' . (int)$kKundengruppe;
                }
            }
        }

        if (\in_array('0', \explode(';', $oNewsletter->cKundengruppe))) {
            if (\is_array($customerGroups) && \count($customerGroups) > 0) {
                $cSQL .= ' OR tkunde.kKundengruppe IS NULL';
            } else {
                $cSQL .= 'tkunde.kKundengruppe IS NULL';
            }
        }
        $cSQL .= ')';

        $manufacturers = \gibHerstellerObjekte($manufacturerIDs, $campaign, $oNewsletter->kSprache);
        $recipients    = $this->db->query(
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

        if (\count($recipients) > 0) {
            $shopURL = \Shop::getURL();
            foreach ($recipients as $oNewsletterEmpfaenger) {
                unset($oKunde);
                $oNewsletterEmpfaenger->cLoeschURL = $shopURL . '/newsletter.php?lang=' .
                    $oNewsletterEmpfaenger->cISO . '&lc=' . $oNewsletterEmpfaenger->cLoeschCode;
                if ($oNewsletterEmpfaenger->kKunde > 0) {
                    $oKunde = new \Kunde($oNewsletterEmpfaenger->kKunde);
                }
                $kKundengruppeTMP = (int)$oNewsletterEmpfaenger->kKundengruppe > 0
                    ? (int)$oNewsletterEmpfaenger->kKundengruppe
                    : 0;

                \versendeNewsletter(
                    $smarty,
                    $oNewsletter,
                    $conf,
                    $oNewsletterEmpfaenger,
                    $products[$kKundengruppeTMP],
                    $manufacturers,
                    $categories[$kKundengruppeTMP],
                    $campaign,
                    $oKunde ?? null
                );
                $upd                     = new \stdClass();
                $upd->dLetzterNewsletter = \date('Y-m-d H:m:s');
                $this->db->update(
                    'tnewsletterempfaenger',
                    'kNewsletterEmpfaenger',
                    (int)$oNewsletterEmpfaenger->kNewsletterEmpfaenger,
                    $upd
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
