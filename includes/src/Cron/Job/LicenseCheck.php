<?php declare(strict_types=1);

namespace JTL\Cron\Job;

use GuzzleHttp\Exception\RequestException;
use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\License\Collection;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\License\Struct\ExsLicense;
use JTL\Plugin\Admin\StateChanger;
use JTL\Plugin\State;
use JTL\Shop;

/**
 * Class LicenseCheck
 * @package JTL\Cron\Job
 */
final class LicenseCheck extends Job
{
    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $manager = new Manager($this->db);
        try {
            $res = $manager->update(true);
            if ($res <= 0) {
                return $this;
            }
        } catch (RequestException $e) {
            return $this;
        }
        $data = $this->db->select('licenses', 'id', $res);
//        $this->setFinished((int)($data->returnCode ?? 0) === 200);
        $this->handleExpiredLicenses($manager);

        return $this;
    }

    private function handleExpiredLicenses(Manager $manager): void
    {
        $mapper     = new Mapper($this->db, $manager);
        $collection = $mapper->getCollection();
        $this->handleExpiredPluginTestLicenses($collection);
    }

    /**
     * @param Collection $collection
     */
    private function handleExpiredPluginTestLicenses(Collection $collection): void
    {
        $expired = $collection->getExpiredActiveTests()->filter(static function (ExsLicense $e) {
            return $e->getType() === ExsLicense::TYPE_PLUGIN;
        });
        if ($expired->count() === 0) {
            return;
        }
        $stateChanger = new StateChanger($this->db, $this->cache);
        foreach ($expired as $item) {
            /** @var ExsLicense $item */
            $stateChanger->deactivate($item->getReferencedItem()->getInternalID(), State::LICENSE_KEY_INVALID);
        }
    }
}
