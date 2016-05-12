<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Notification
 */
class Notification implements IteratorAggregate
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
     */
    public function add($type, $title, $description = null)
    {
        $this->addNotify(new NotificationEntry($type, $title, $description));
    }

    /**
     * @param NotificationEntry $notify
     */
    public function addNotify(NotificationEntry $notify)
    {
        $this->array[] = $notify;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator() {
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

        $notify = new Notification();
        $template = Template::getInstance();
        $writeableDirs = checkWriteables();
        $permissionStat = getPermissionStats($writeableDirs);

        if ($permissionStat->nCountInValid > 0) {
            $notify->add(NotificationEntry::TYPE_WARNING, "Verzeichnis-Check", "Es sind {$permissionStat->nCountInValid} Verzeichnisse nicht beschreibbar. Eine &Uuml;bersicht finden Sie im <a href=\"permissioncheck.php\">Verzeichnis-Check</a>");
        }

        if (is_dir(PFAD_ROOT . 'install')) {
            $notify->add(NotificationEntry::TYPE_WARNING, "Install-Verzeichnis", "Bitte l&ouml;schen Sie das Installationsverzeichnis \"/install/\" im Shop-Wurzelverzeichnis.");
        }

        if (JTL_VERSION != $template->getShopVersion()) {
            $notify->add(NotificationEntry::TYPE_WARNING, "Template-Version", "Achtung, Ihre Template-Version unterscheidet sich von Ihrer Shop-Version.<br />Bitte aktualisieren Sie Ihr Template bzw. aktivieren Sie die aktuelle Template-Version unter <a href=\"shoptemplate.php\">Darstellung > Template</a>. Weitere Hilfe zu Template-Updates finden Sie im <a href=\"http://developer.jtl-software.de/projects/template-dev/wiki/Template_Updates\" title=\"Wiki\"><i class=\"fa fa-external-link\"></i> Wiki</a>");
        }

        return $notify;
    }
}
