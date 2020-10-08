<?php declare(strict_types=1);

namespace JTL\License\Struct;

use DateTime;
use stdClass;

/**
 * Class ExsLicense
 * @package JTL\License
 */
class ExpiredExsLicense extends ExsLicense
{
    /**
     * @param stdClass $data
     * @throws \Exception
     */
    public function initFromPluginData(stdClass $data): void
    {
        $this->setExsID($data->exsID);
        $this->setQueryDate(new DateTime());
        $license = new License();
        $license->setIsBound(true);
        $license->setKey($data->cPluginID);
        $license->setExpired(true);
        $license->setCreated(new DateTime());
        $license->setType(self::TYPE_PLUGIN);
        $this->setLicense($license);
        $this->setID($data->cPluginID);
        $this->setState(self::STATE_ACTIVE);
        $subscription = new Subscription();
        $subscription->setExpired(true);
        $license->setSubscription($subscription);
        $vendor = new Vendor();
        $vendor->setName($data->cAutor);
        $vendor->setHref($data->cURL);
        $this->setVendor($vendor);
        $this->setLinks([]);
    }
}
