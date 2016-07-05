<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Notification.
 */
class Notification implements IteratorAggregate, Countable
{
    use SingletonTrait;

    private $array = [];

    /**
     * @param int         $type
     * @param string      $title
     * @param string|null $description
     * @param string|null $url
     */
    public function add($type, $title, $description = null, $url = null)
    {
        $this->addNotify(new NotificationEntry($type, $title, $description, $url));
    }

    /**
     * @param NotificationEntry $notify
     */
    public function addNotify(NotificationEntry $notify)
    {
        $this->array[] = $notify;
    }

    /**
     * @return highest type in record
     */
    public function getHighestType()
    {
        $type = NotificationEntry::TYPE_NONE;
        foreach ($this as $notify) {
            if ($notify->getType() > $type) {
                $type = $notify->getType();
            }
        }

        return $type;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->array);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        usort($this->array, function ($a, $b) {
            if ($a->getType() > $b->getType()) {
                return -1;
            } elseif ($a->getType() < $b->getType()) {
                return 1;
            }

            return 0;
        });

        return new ArrayIterator($this->array);
    }

    /**
     * Build default system notifications.
     * @todo Remove translated messages
     */
    public function buildDefault()
    {
        $status = Status::getInstance();

        if ($status->hasPendingUpdates()) {
            $this->add(NotificationEntry::TYPE_DANGER, 'Systemupdate', 'Ein Datenbank-Update ist zwingend notwendig', 'dbupdater.php');
        }

        if (!$status->validFolderPermissions()) {
            $this->add(NotificationEntry::TYPE_DANGER, 'Dateisystem', "Es sind Verzeichnisse nicht beschreibbar.", 'permissioncheck.php');
        }

        if ($status->hasInstallDir()) {
            $this->add(NotificationEntry::TYPE_WARNING, 'System', 'Bitte l&ouml;schen Sie das Installationsverzeichnis "/install/" im Shop-Wurzelverzeichnis.');
        }

        if ($status->hasDifferentTemplateVersion()) {
            $this->add(NotificationEntry::TYPE_WARNING, 'Template', 'Ihre Template-Version unterscheidet sich von Ihrer Shop-Version.<br />Weitere Hilfe zu Template-Updates finden Sie im <i class="fa fa-external-link"></i> Wiki', 'shoptemplate.php');
        }

        if ($status->hasActiveProfiler()) {
            $this->add(NotificationEntry::TYPE_WARNING, 'Plugin', 'Der Profiler ist aktiv und kann zu starken Leistungseinbu&szlig;en im Shop f&uuml;hren.');
        }

        if ($subscription = $status->getSubscription()) {
            if ((int) $subscription->bUpdate === 1) {
                if ((int) $subscription->nDayDiff <= 0) {
                    $this->add(NotificationEntry::TYPE_WARNING, 'Subscription', 'Ihre Subscription ist abgelaufen. Jetzt erneuern.', 'http://jtl-url.de/subscription');
                } else {
                    $this->add(NotificationEntry::TYPE_INFO, 'Subscription', "Ihre Subscription l&auml;uft in {$subscription->nDayDiff} Tagen ab.", 'http://jtl-url.de/subscription');
                }
            }
        }
    }
}
