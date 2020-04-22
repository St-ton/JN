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
     * @param string $pluginID
     * @return ExsLicense|null
     */
    public function getForPluginID(string $pluginID): ?ExsLicense
    {
        return $this->filter(static function (ExsLicense $e) use ($pluginID) {
            return $e->getID() === $pluginID;
        })->first();
    }
}
