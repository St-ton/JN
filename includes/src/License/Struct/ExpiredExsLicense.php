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
    public function init(stdClass $data): void
    {
        $this->setExsID($data->exsID);
        $this->setQueryDate(new DateTime());
        $license = new License();
        $license->setExpired(true);
        $this->setLicense($license);
        $this->setID($data->cPluginID);
        $this->setState(self::STATE_ACTIVE);
    }
}
