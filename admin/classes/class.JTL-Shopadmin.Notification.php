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

    /**
     * @var array
     */
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
     * @return int - highest type in record
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
            }
            if ($a->getType() < $b->getType()) {
                return 1;
            }

            return 0;
        });

        return new ArrayIterator($this->array);
    }

    /**
     * Build default system notifications.
     *
     * @todo Remove translated messages
     * @return $this
     */
    public function buildDefault()
    {
        /** @var Status $status */
        $status     = Status::getInstance();
        $confGlobal = Shop::getSettings([CONF_GLOBAL, CONF_ARTIKELUEBERSICHT]);

        if ($status->hasPendingUpdates()) {
            $this->add(NotificationEntry::TYPE_DANGER, 'Systemupdate', 'Ein Datenbank-Update ist zwingend notwendig', 'dbupdater.php');
        }

        if (!$status->validFolderPermissions()) {
            $this->add(NotificationEntry::TYPE_DANGER, 'Dateisystem', "Es sind Verzeichnisse nicht beschreibbar.", 'permissioncheck.php');
        }

        if ($status->hasInstallDir()) {
            $this->add(NotificationEntry::TYPE_WARNING, 'System', 'Bitte l&ouml;schen Sie das Installationsverzeichnis "/install/" im Shop-Wurzelverzeichnis.');
        }

        if (!$status->validDatabaseStruct()) {
            $this->add(NotificationEntry::TYPE_DANGER, 'Datenbank', 'Es liegen Fehler in der Datenbankstruktur vor.', 'dbcheck.php');
        }

        if (!$status->validFileStruct()) {
            $this->add(NotificationEntry::TYPE_DANGER, 'Filesystem', 'Es liegen Fehler in der Shop-Dateistruktur vor.', 'filecheck.php');
        }

        if ($status->hasDifferentTemplateVersion()) {
            $this->add(NotificationEntry::TYPE_WARNING, 'Template', 'Ihre Template-Version unterscheidet sich von Ihrer Shop-Version.<br />Weitere Hilfe zu Template-Updates finden Sie im <i class="fa fa-external-link"></i> Wiki', 'shoptemplate.php');
        }

        if ($status->hasMobileTemplateIssue()) {
            $this->add(NotificationEntry::TYPE_INFO, 'Template', 'Sie nutzen ein Full-Responsive-Template. Die Aktivierung eines separaten Mobile-Templates ist in diesem Fall nicht notwendig.', 'shoptemplate.php');
        }

        if ($status->hasStandardTemplateIssue()) {
            $this->add(NotificationEntry::TYPE_WARNING, 'Template', 'Sie haben kein Standard-Template aktiviert!', 'shoptemplate.php');
        }

        if ($status->hasActiveProfiler()) {
            $this->add(NotificationEntry::TYPE_WARNING, 'Plugin', 'Der Profiler ist aktiv. Dies kann zu starken Leistungseinbu&szlig;en im Shop f&uuml;hren.');
        }

        if ((int)$confGlobal['global']['anti_spam_method'] === 7 && !reCaptchaConfigured()) {
            $this->add(NotificationEntry::TYPE_WARNING, 'Konfiguration', 'Sie haben Google reCaptcha als Spamschutz-Methode gew&auml;hlt, aber Website- und/oder Geheimer Schl&uuml;ssel nicht angegeben.', 'einstellungen.php?kSektion=1#anti_spam_method');
        }

        if ($subscription = $status->getSubscription()) {
            if ((int)$subscription->bUpdate === 1) {
                if ((int)$subscription->nDayDiff <= 0) {
                    $this->add(NotificationEntry::TYPE_WARNING, 'Subscription', 'Ihre Subscription ist abgelaufen. Jetzt erneuern.', 'http://jtl-url.de/subscription');
                } else {
                    $this->add(NotificationEntry::TYPE_INFO, 'Subscription', "Ihre Subscription l&auml;uft in {$subscription->nDayDiff} Tagen ab.", 'http://jtl-url.de/subscription');
                }
            }
        }

        if ($status->hasInvalidPollCoupons()) {
            $this->add(NotificationEntry::TYPE_WARNING, 'Umfrage', 'In einer Umfrage wird ein Kupon verwendet, welcher inaktiv ist oder nicht mehr existiert.');
        }

        if ($confGlobal['artikeluebersicht']['suche_fulltext'] === 'Y'
            && (!Shop::DB()->query("SHOW INDEX FROM tartikel WHERE KEY_NAME = 'idx_tartikel_fulltext'", 1)
                || !Shop::DB()->query("SHOW INDEX FROM tartikelsprache WHERE KEY_NAME = 'idx_tartikelsprache_fulltext'", 1))) {
            $this->add(NotificationEntry::TYPE_WARNING, 'Der Volltextindex ist nicht vorhanden!', 'sucheinstellungen.php');
        }

        if ($status->usesDeprecatedPriceImages()) {
            $this->add(NotificationEntry::TYPE_WARNING, 'Konfiguration',
                'Sie nutzen Grafikpreise. Diese Funktion ist als "deprecated" markiert.<br/>Bitte beachten Sie die Hinweise unter "Storefront->Artikel->Preisanzeige".',
                'preisanzeige.php');
        }

        return $this;
    }
}
