<?php declare(strict_types=1);

namespace JTL\License;

use JTL\License\Struct\ExsLicense;
use JTL\License\Struct\License;

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
        return $this->getBound();
    }
    /**
     * @return $this
     */
    public function getBound(): self
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
     * @param string $exsID
     * @return ExsLicense|null
     */
    public function getForExsID(string $exsID): ?ExsLicense
    {
        return $this->filter(static function (ExsLicense $e) use ($exsID) {
            return $e->getExsID() === $exsID;
        })->first();
    }

    /**
     * @param string $licenseKey
     * @return ExsLicense|null
     */
    public function getForLicenseKey(string $licenseKey): ?ExsLicense
    {
        return $this->filter(static function (ExsLicense $e) use ($licenseKey) {
            return $e->getLicense()->getKey() === $licenseKey;
        })->first();
    }

    /**
     * @return $this
     */
    public function getActiveExpired(): self
    {
        return $this->getBoundExpired();
    }

    /**
     * @return $this
     */
    public function getBoundExpired(): self
    {
        return $this->getBound()->filter(static function (ExsLicense $e) {
            $ref = $e->getReferencedItem();

            return $ref !== null
                && $ref->isActive()
                && ($e->getLicense()->isExpired() || $e->getLicense()->getSubscription()->isExpired());
        });
    }

    /**
     * @return $this
     */
    public function getExpiredActiveTests(): self
    {
        return $this->getExpiredBoundTests();
    }

    /**
     * @return $this
     */
    public function getExpiredBoundTests(): self
    {
        return $this->getBoundExpired()->filter(static function (ExsLicense $e) {
            return $e->getLicense()->getType() === License::TYPE_TEST;
        });
    }

    /**
     * @return $this
     */
    public function getPlugins(): self
    {
        return $this->filter(static function (ExsLicense $e) {
            return $e->getType() === ExsLicense::TYPE_PLUGIN || $e->getType() === ExsLicense::TYPE_PORTLET;
        });
    }

    /**
     * @return $this
     */
    public function getTemplates(): self
    {
        return $this->filter(static function (ExsLicense $e) {
            return $e->getType() === ExsLicense::TYPE_TEMPLATE;
        });
    }

    /**
     * @return $this
     */
    public function getPortlets(): self
    {
        return $this->filter(static function (ExsLicense $e) {
            return $e->getType() === ExsLicense::TYPE_PORTLET;
        });
    }

    /**
     * @return $this
     */
    public function getInstalled(): self
    {
        return $this->getBound()->filter(static function (ExsLicense $e) {
            return $e->getReferencedItem() !== null;
        });
    }

    /**
     * @return $this
     */
    public function getUpdateableItems(): self
    {
        return $this->getBound()->getInstalled()->filter(static function (ExsLicense $e) {
            return $e->getReferencedItem()->hasUpdate() === true;
        });
    }

    /**
     * @return $this
     */
    public function getExpired(): self
    {
        return $this->getBound()->filter(static function (ExsLicense $e) {
            return $e->getLicense()->isExpired() || $e->getLicense()->getSubscription()->isExpired();
        });
    }

    /**
     * @param int $days
     * @return $this
     */
    public function getAboutToBeExpired(int $days = 28): self
    {
        return $this->getBound()->filter(static function (ExsLicense $e) use ($days) {
            $license = $e->getLicense();

            return (!$license->isExpired()
                    && $license->getDaysRemaining() > 0
                    && $license->getDaysRemaining() < $days)
                || (!$license->getSubscription()->isExpired()
                    && $license->getSubscription()->getDaysRemaining() > 0
                    && $license->getSubscription()->getDaysRemaining() < $days
                );
        });
    }
}
