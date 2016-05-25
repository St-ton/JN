<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Notification
 */
class Notification implements IteratorAggregate, Countable
{
    private $array;

    /**
     * Notification constructor.
     */
    public function __construct()
    {
        $this->array = [];
    }

    /**
     * @param int $type
     * @param string $title
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
     * @return mixed  - highest type in record
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
        return new ArrayIterator($this->array);
    }

    /**
     * Build default system notifications
     * @todo Remove translated messages
     *
     * @return Notification
     */
    public static function buildDefault()
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dashboard_inc.php';
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'permissioncheck_inc.php';

        $notify         = new Notification();
        $updater        = new Updater();
        $template       = Template::getInstance();
        $writeableDirs  = checkWriteables();
        $permissionStat = getPermissionStats($writeableDirs);
        $confGlobal     = Shop::getSettings(array(CONF_GLOBAL));
        
        if ($updater->hasPendingUpdates()) {
            $notify->add(NotificationEntry::TYPE_DANGER, "Systemupdate", "Ein Datenbank-Update ist zwingend notwendig", "dbupdater.php");
        }

        if ($permissionStat->nCountInValid > 0) {
            $notify->add(NotificationEntry::TYPE_WARNING, "Dateisystem", "Es sind {$permissionStat->nCountInValid} Verzeichnisse nicht beschreibbar.", "permissioncheck.php");
        }

        if (is_dir(PFAD_ROOT . 'install')) {
            $notify->add(NotificationEntry::TYPE_DANGER, "System", "Bitte l&ouml;schen Sie das Installationsverzeichnis \"/install/\" im Shop-Wurzelverzeichnis.");
        }

        if (JTL_VERSION != $template->getShopVersion()) {
            $notify->add(NotificationEntry::TYPE_WARNING, "Template", "Ihre Template-Version unterscheidet sich von Ihrer Shop-Version.<br />Weitere Hilfe zu Template-Updates finden Sie im <i class=\"fa fa-external-link\"></i> Wiki", "shoptemplate.php");
        }
        
        if (Profiler::getIsActive() !== 0) {
            $notify->add(NotificationEntry::TYPE_WARNING, "Plugin", "Der Profiler ist aktiv und kann zu starken Leistungseinbu&szlig;en im Shop f&uuml;hren.");
        }

        if ($confGlobal['global']['anti_spam_method'] == 7 && !reCaptchaConfigured()) {
            $notify->add(NotificationEntry::TYPE_WARNING, "Konfiguration", "Sie haben Google reCaptcha als Spamschutz-Methode gew&auml;hlt, aber Website- und/oder Geheimer Schl&uuml;ssel nicht angegeben.", 'einstellungen.php?kSektion=1#anti_spam_method');
        }

        return $notify;
    }
}
