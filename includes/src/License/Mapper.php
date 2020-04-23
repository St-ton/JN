<?php declare(strict_types=1);

namespace JTL\License;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\License\Struct\ExsLicense;
use JTL\License\Struct\ReferencedItem;
use JTL\License\Struct\ReferencedPlugin;
use JTL\Shop;
use JTLShop\SemVer\Version;

/**
 * Class Mapper
 * @package JTL\License
 */
class Mapper
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * Mapper constructor.
     * @param DbInterface $db
     * @param Manager     $manager
     */
    public function __construct(DbInterface $db, Manager $manager)
    {
        $this->db      = $db;
        $this->manager = $manager;
    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        $collection = new Collection();
        $data       = $this->manager->getLicenseData();
        if ($data === null) {
            return $collection;
        }
        foreach ($data->extensions as $extension) {
            if ($extension->id === 'jtl_paypal_shop5') {
                $extension->id = 'jtl_paypal';
            }
            $esxLicense = new ExsLicense($extension);
            $esxLicense->setQueryDate($data->timestamp);
            $esxLicense->setState(ExsLicense::STATE_ACTIVE);
            if ($esxLicense->getType() === ExsLicense::TYPE_PLUGIN) {
                $plugin = new ReferencedPlugin($this->db, $extension->id, $esxLicense->getReleases()->getAvailable());
                $esxLicense->setReferencedItem($plugin);
            }
            $collection->push($esxLicense);
        }
        foreach ($data->unbound as $extension) {
            $esxLicense = new ExsLicense($extension);
            $esxLicense->setQueryDate($data->timestamp);
            $esxLicense->setState(ExsLicense::STATE_UNBOUND);
            $collection->push($esxLicense);
        }

        return $collection;
    }
}
