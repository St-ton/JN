<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Cron\Job;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\DB\ReturnType;
use JTL\Kampagne;
use JTL\Customer\Kunde;
use JTL\Shop;

/**
 * Class Newsletter
 * @package JTL\Cron\Job
 */
final class Newsletter extends Job
{
    /**
     * @inheritdoc
     */
    public function hydrate($data)
    {
        parent::hydrate($data);
        if (\JOBQUEUE_LIMIT_M_NEWSLETTER > 0) {
            $this->setLimit((int)\JOBQUEUE_LIMIT_M_NEWSLETTER);
        }

        return $this;
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

        $conf = Shop::getSettings([\CONF_NEWSLETTER]);

        $instance = new \JTL\Newsletter\Newsletter($this->db, $conf);
        $instance->initSmarty();

        $productIDs      = $instance->getKeys($oNewsletter->cArtikel, true);
        $manufacturerIDs = $instance->getKeys($oNewsletter->cHersteller);
        $categoryIDs     = $instance->getKeys($oNewsletter->cKategorie);
        $customerGroups  = $instance->getKeys($oNewsletter->cKundengruppe);
        $campaign        = new Kampagne($oNewsletter->kKampagne);
        if (\count($customerGroups) === 0) {
            $this->setFinished(true);
            $this->db->delete('tnewsletterqueue', 'kNewsletter', $queueEntry->foreignKeyID);

            return $this;
        }
        $products   = [];
        $categories = [];
        foreach ($customerGroups as $groupID) {
            $products[$groupID]   = $instance->getProducts($productIDs, $campaign, $groupID, (int)$oNewsletter->kSprache);
            $categories[$groupID] = $instance->getCategories($categoryIDs, $campaign);
        }
        $cgSQL = 'AND (tkunde.kKundengruppe IN (' . \implode(',', $customerGroups) . ') ';
        if (\in_array(0, $customerGroups, true)) {
            $cgSQL .= ' OR tkunde.kKundengruppe IS NULL';
        }
        $cgSQL        .= ')';
        $manufacturers = $instance->getManufacturers($manufacturerIDs, $campaign, $oNewsletter->kSprache);
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
                LIMIT ' . $queueEntry->tasksExecuted . ', ' . $queueEntry->taskLimit,
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($recipients) > 0) {
            $shopURL = Shop::getURL();
            foreach ($recipients as $recipient) {
                $recipient->cLoeschURL = $shopURL . '/newsletter.php?lang=' .
                    $recipient->cISO . '&lc=' . $recipient->cLoeschCode;
                $customer              = $recipient->kKunde > 0
                    ? new Kunde($recipient->kKunde)
                    : null;
                $cgID                  = (int)$recipient->kKundengruppe > 0
                    ? (int)$recipient->kKundengruppe
                    : 0;

                $instance->send(
                    $oNewsletter,
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
                ++$queueEntry->taskLimit;
            }
            $this->setFinished(false);
        } else {
            $this->setFinished(true);
            $this->db->delete('tnewsletterqueue', 'kNewsletter', $queueEntry->foreignKeyID);
        }

        return $this;
    }
}
