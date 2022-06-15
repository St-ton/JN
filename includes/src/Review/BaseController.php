<?php declare(strict_types=1);

namespace JTL\Review;

use JTL\Cache\JTLCacheInterface;
use JTL\Customer\Customer;
use JTL\DB\DbInterface;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use stdClass;

/**
 * Class BaseController
 * @package JTL\Review
 * @deprecated since 5.2.0
 */
abstract class BaseController
{
    /**
     * @var DbInterface
     */
    protected DbInterface $db;

    /**
     * @var array
     */
    protected array $config;

    /**
     * @var JTLSmarty
     */
    protected JTLSmarty $smarty;

    /**
     * @var JTLCacheInterface
     */
    protected JTLCacheInterface $cache;

    /**
     * @var AlertServiceInterface
     */
    protected AlertServiceInterface $alertService;

    /**
     * @param int    $productID
     * @param string $activate
     * @return bool
     */
    public function updateAverage(int $productID, string $activate): bool
    {
        return false;
    }

    /**
     * @param ReviewModel $review
     * @return float
     */
    public function addReward(ReviewModel $review): float
    {
        return 0.0;
    }

    /**
     * @param int   $customerID
     * @param float $reward
     * @return int
     */
    public function increaseCustomerBalance(int $customerID, float $reward): int
    {
        return $this->db->getAffectedRows(
            'UPDATE tkunde
                SET fGuthaben = fGuthaben + :rew
                WHERE kKunde = :cid',
            ['cid' => $customerID, 'rew' => $reward]
        );
    }

    /**
     * @param ReviewBonusModel $reviewBonus
     * @return bool
     */
    public function sendRewardMail(ReviewBonusModel $reviewBonus): bool
    {
        $obj                          = new stdClass();
        $obj->tkunde                  = new Customer($reviewBonus->customerID);
        $obj->oBewertungGuthabenBonus = $reviewBonus->rawObject();
        $mailer                       = Shop::Container()->get(Mailer::class);
        $mail                         = new Mail();

        return $mailer->send($mail->createFromTemplateID(\MAILTEMPLATE_BEWERTUNG_GUTHABEN, $obj));
    }
}
