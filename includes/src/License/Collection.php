<?php declare(strict_types=1);

namespace JTL\License;

use JTL\License\Struct\ExsLicense;

/**
 * Class Collection
 * @package JTL\License
 */
class Collection extends \Illuminate\Support\Collection
{
    /**
     * @return $this
     */
    public function getActive(): self
    {
        return $this->filter(static function (ExsLicense $e) {
            return $e->getState() === ExsLicense::STATE_ACTIVE;
        });
    }

    /**
     * @return $this
     */
    public function getUnbound(): self
    {
        return $this->filter(static function (ExsLicense $e) {
            return $e->getState() === ExsLicense::STATE_UNBOUND;
        });
    }

    /**
     * @param string $itemID
     * @return ExsLicense|null
     */
    public function getForItemID(string $itemID): ?ExsLicense
    {
        return $this->filter(static function (ExsLicense $e) use ($itemID) {
            return $e->getID() === $itemID;
        })->first();
    }

    /**
     * @return $this
     */
    public function getActiveExpired(): self
    {
        return $this->getActive()->filter(static function (ExsLicense $e) {
            $ref = $e->getReferencedItem();

            return $ref !== null
                && $ref->isActive()
                && ($e->getLicense()->isExpired()
                    || ($e->getLicense()->getSubscription() !== null &&
                        $e->getLicense()->getSubscription()->isExpired()));
        });
    }
}
