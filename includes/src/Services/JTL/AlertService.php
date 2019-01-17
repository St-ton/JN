<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace Services\JTL;

use Alert;
use Tightenco\Collect\Support\Collection;

/**
 * Class AlertService
 */
class AlertService implements AlertServiceInterface
{
    /**
    * @var Collection
    */
    private $alertList;

    /**
     * Alertservice constructor.
     */
    public function __construct()
    {
        $this->alertList = new Collection();
        $this->initFromSession();
    }

    /**
     * @inheritdoc
     */
    public function initFromSession(): void
    {
        $alerts = $_SESSION['alerts'] ?? '';

        if (!empty($alerts)) {
            foreach ($alerts as $alertSerialized) {
                $this->pushAlert(unserialize($alertSerialized, ['allowed_classes', 'Alert']));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function addAlert(string $type, string $message, string $key, array $options = null): Alert
    {
        $alert = new Alert($type, $message, $key, $options);
        $this->pushAlert($alert);

        return $alert;
    }

    /**
     * @inheritdoc
     */
    public function getAlert(string $key): ?Alert
    {
        return $this->alertList->filter(function (Alert $alert) use ($key) {
            return $alert->getKey() === $key;
        })->pop();
    }

    /**
     * @inheritdoc
     */
    public function displayAlertByKey(string $key): void
    {
        if ($alert = $this->getAlert($key)) {
            $alert->display();
        }
    }

    /**
     * @inheritdoc
     */
    public function getAlertList(): Collection
    {
        return $this->alertList;
    }

    /**
     * @inheritdoc
     */
    public function alertTypeExists(string $type): bool
    {
        foreach ($this->getAlertList() as $alert) {
            if ($alert->getType() === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function removeAlertByKey(string $key): void
    {
        $key = $this->getAlertList()->search(function (Alert $alert) use ($key) {
            return $alert->getKey() === $key;
        });
        if ($key !== false) {
            $this->getAlertList()->pull($key);
        }
    }

    /**
     * @param Alert $alert
     */
    private function pushAlert(Alert $alert): void
    {
        $this->removeAlertByKey($alert->getKey());
        $this->getAlertList()->push($alert);
    }
}
