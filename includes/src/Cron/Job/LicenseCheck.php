<?php declare(strict_types=1);

namespace JTL\Cron\Job;

use GuzzleHttp\Exception\RequestException;
use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\Events\Dispatcher;
use JTL\License\Collection;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\License\Struct\ExsLicense;
use JTL\Plugin\Admin\StateChanger;
use JTL\Plugin\Helper as PluginHelper;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;
use JTL\Template\BootChecker;

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
        $this->handleExpiredLicenses($manager);
        $data = $this->db->select('licenses', 'id', $res);
        $this->setFinished((int)($data->returnCode ?? 0) === 200);

        return $this;
    }

    /**
     * @param Manager $manager
     */
    private function handleExpiredLicenses(Manager $manager): void
    {
        $mapper     = new Mapper($this->db, $manager);
        $collection = $mapper->getCollection();
        $this->handleExpiredPluginTestLicenses($collection);
        $this->notifyPlugins($collection);
        $this->notifyTemplates($collection);
    }

    /**
     * @param Collection $collection
     */
    private function notifyTemplates(Collection $collection): void
    {
        foreach ($collection->getTemplates()->getActiveExpired() as $license) {
            /** @var ExsLicense $license */
            $this->logger->info('License for template ' . $license->getID() . ' is expired.');
            $bootstrapper = BootChecker::bootstrap($license->getID());
            if ($bootstrapper !== null) {
                $bootstrapper->licenseExpired($license);
            }
        }
    }

    /**
     * @param Collection $collection
     */
    private function notifyPlugins(Collection $collection): void
    {
        $dispatcher = Dispatcher::getInstance();
        $loader     = new PluginLoader($this->db, $this->cache);
        foreach ($collection->getPlugins()->getActiveExpired() as $license) {
            /** @var ExsLicense $license */
            $this->logger->info('License for plugin ' . $license->getID() . ' is expired.');
            if (($p = PluginHelper::bootstrap($license->getReferencedItem()->getInternalID(), $loader)) !== null) {
                $p->boot($dispatcher);
                $p->licenseExpired($license);
            }
        }
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
        foreach ($expired as $license) {
            /** @var ExsLicense $license */
            $this->logger->warning('Plugin ' . $license->getID() . ' disabled due to expired test license.');
            $stateChanger->deactivate($license->getReferencedItem()->getInternalID(), State::LICENSE_KEY_INVALID);
        }
    }
}
