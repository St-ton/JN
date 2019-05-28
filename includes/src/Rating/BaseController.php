<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Rating;

use JTL\Cache\JTLCacheInterface;
use JTL\Customer\Kunde;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTLSmarty;
use stdClass;

/**
 * Class BaseController
 * @package JTL\Rating
 */
abstract class BaseController
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var JTLSmarty
     */
    protected $smarty;

    /**
     * @var JTLCacheInterface
     */
    protected $cache;

    /**
     * @var AlertServiceInterface
     */
    protected $alertService;

    /**
     * @param int    $productID
     * @param string $activate
     * @return bool
     */
    public function updateAverage(int $productID, string $activate): bool
    {
        $sql       = $activate === 'Y' ? ' AND nAktiv = 1' : '';
        $countData = $this->db->query(
            'SELECT COUNT(*) AS nAnzahl
            FROM tbewertung
            WHERE kArtikel = ' . $productID . $sql,
            ReturnType::SINGLE_OBJECT
        );

        if ((int)$countData->nAnzahl === 1) {
            $sql = '';
        } elseif ((int)$countData->nAnzahl === 0) {
            $this->db->delete('tartikelext', 'kArtikel', $productID);

            return false;
        }
        $avg = $this->db->query(
            'SELECT (SUM(nSterne) / COUNT(*)) AS fDurchschnitt
            FROM tbewertung
            WHERE kArtikel = ' . $productID . $sql,
            ReturnType::SINGLE_OBJECT
        );
        if (isset($avg->fDurchschnitt) && $avg->fDurchschnitt > 0) {
            $this->db->delete('tartikelext', 'kArtikel', $productID);
            $ext                          = new stdClass();
            $ext->kArtikel                = $productID;
            $ext->fDurchschnittsBewertung = (float)$avg->fDurchschnitt;

            $this->db->insert('tartikelext', $ext);
        }

        return true;
    }

    /**
     * @param RatingModel $rating
     * @return float
     */
    public function addReward(RatingModel $rating): float
    {
        $reward = 0.0;
        if ($this->config['bewertung']['bewertung_guthaben_nutzen'] !== 'Y') {
            return $reward;
        }
        $maxBalance    = (float)$this->config['bewertung']['bewertung_max_guthaben'];
        $level2balance = (float)$this->config['bewertung']['bewertung_stufe2_guthaben'];
        $level1balance = (float)$this->config['bewertung']['bewertung_stufe1_guthaben'];
        $ratingBonus   = $this->db->queryPrepared(
            'SELECT SUM(fGuthabenBonus) AS fGuthabenProMonat
            FROM tbewertungguthabenbonus
            WHERE kKunde = :cid
                AND kBewertung != :rid
                AND YEAR(dDatum) = :dyear
                AND MONTH(dDatum) = :dmonth',
            [
                'cid'    => $rating->customerID,
                'rid'    => $rating->id,
                'dyear'  => \date('Y'),
                'dmonth' => \date('m')
            ],
            ReturnType::SINGLE_OBJECT
        );
        if ((float)$ratingBonus->fGuthabenProMonat > $maxBalance) {
            return $reward;
        }
        if ((int)$this->config['bewertung']['bewertung_stufe2_anzahlzeichen'] <= mb_strlen($rating->content)) {
            $reward = ((float)$ratingBonus->fGuthabenProMonat + $level2balance) > $maxBalance
                ? $maxBalance - (float)$ratingBonus->fGuthabenProMonat
                : $level2balance;
        } else {
            $reward = ((float)$ratingBonus->fGuthabenProMonat + $level1balance) > $maxBalance
                ? $maxBalance - (float)$ratingBonus->fGuthabenProMonat
                : $level1balance;
        }
        $this->increaseCustomerBalance($rating->customerID, $reward);

        $ratingBonus = RatingBonusModel::loadByAttributes(
            ['customerID' => $rating->customerID, 'ratingID' => $rating->id],
            $this->db
        );
        /** @var $ratingBonus RatingBonusModel */
        $ratingBonus->bonus      = $reward;
        $ratingBonus->ratingID   = $rating->id;
        $ratingBonus->customerID = $rating->customerID;
        $ratingBonus->date       = 'NOW()';
        $ratingBonus->save();
        $this->sendRewardMail($ratingBonus);

        return $reward;
    }

    /**
     * @param int   $customerID
     * @param float $reward
     * @return int
     */
    public function increaseCustomerBalance(int $customerID, float $reward): int
    {
        return $this->db->queryPrepared(
            'UPDATE tkunde
                SET fGuthaben = fGuthaben + :rew
                WHERE kKunde = :cid',
            ['cid' => $customerID, 'rew' => $reward],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param RatingBonusModel $ratingBonus
     * @return bool
     */
    public function sendRewardMail(RatingBonusModel $ratingBonus): bool
    {
        $obj                          = new stdClass();
        $obj->tkunde                  = new Kunde($ratingBonus->customerID);
        $obj->oBewertungGuthabenBonus = $ratingBonus->rawObject();
        $mailer                       = Shop::Container()->get(Mailer::class);
        $mail                         = new Mail();

        return $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BEWERTUNG_GUTHABEN, $obj));
    }
}
