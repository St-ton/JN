<?php declare(strict_types=1);

namespace JTL\License;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\License\Struct\ExsLicense;
use JTL\License\Struct\ReferencedItem;
use JTL\License\Struct\ReferencedPlugin;
use JTL\License\Struct\ReferencedTemplate;
use JTL\Shop;
use JTLShop\SemVer\Version;
use stdClass;

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
            $esxLicense = new ExsLicense($extension);
            $esxLicense->setQueryDate($data->timestamp);
            $esxLicense->setState(ExsLicense::STATE_ACTIVE);
            $this->setReference($esxLicense, $extension);
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

    /**
     * @param ExsLicense $esxLicense
     * @param stdClass   $license
     */
    private function setReference(ExsLicense $esxLicense, stdClass $license): void
    {
        switch ($esxLicense->getType()) {
            case ExsLicense::TYPE_PLUGIN:
                $plugin = new ReferencedPlugin($this->db, $license->exsid, $esxLicense->getReleases()->getAvailable());
                $esxLicense->setReferencedItem($plugin);
                break;
            case ExsLicense::TYPE_TEMPLATE:
                $template = new ReferencedTemplate($license->exsid, $esxLicense->getReleases()->getAvailable());
                $esxLicense->setReferencedItem($template);
                break;
            case ExsLicense::TYPE_PORTLET:
                // @todo
                break;
        }
    }
}
