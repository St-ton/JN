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
        $oNewsletter->kNewsletter = (int)$oNewsletter->kNewsletter;
        $oNewsletter->kSprache    = (int)$oNewsletter->kSprache;
        $oNewsletter->kKampagne   = (int)$oNewsletter->kKampagne;
        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'newsletter_inc.php';
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
        foreach ($customerGroups as $groupID) {
            $products[$groupID]   = \gibArtikelObjekte($productIDs, $campaign, $groupID, (int)$oNewsletter->kSprache);
            $categories[$groupID] = \gibKategorieObjekte($categoryIDs, $campaign);
        }
        $cgSQL = 'AND (tkunde.kKundengruppe IN (' . \implode(',', $customerGroups) . ') ';
        if (\in_array(0, $customerGroups, true)) {
            $cgSQL .= ' OR tkunde.kKundengruppe IS NULL';
        }
        $cgSQL        .= ')';
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
                    AND tnewsletterempfaenger.nAktiv = 1 ' . $cgSQL . '
                ORDER BY tnewsletterempfaenger.kKunde
                LIMIT ' . $queueEntry->nLimitN . ', ' . $queueEntry->nLimitM,
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($recipients) > 0) {
            $shopURL = \Shop::getURL();
            foreach ($recipients as $recipient) {
                $recipient->cLoeschURL = $shopURL . '/newsletter.php?lang=' .
                    $recipient->cISO . '&lc=' . $recipient->cLoeschCode;
                $customer              = $recipient->kKunde > 0
                    ? new \Kunde($recipient->kKunde)
                    : null;
                $cgID                  = (int)$recipient->kKundengruppe > 0
                    ? (int)$recipient->kKundengruppe
                    : 0;

                \versendeNewsletter(
                    $smarty,
                    $oNewsletter,
                    $conf,
                    $recipient,
                    $products[$cgID],
                    $manufacturers,
                    $categories[$cgID],
                    $campaign,
                    $customer ?? null
                );
                $this->db->update(
                    'tnewsletterempfaenger',
                    'kNewsletterEmpfaenger',
                    (int)$recipient->kNewsletterEmpfaenger,
                    (object)['dLetzterNewsletter' => \date('Y-m-d H:m:s')]
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
