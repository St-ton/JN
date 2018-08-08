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
     * @var NotificationEntry[]
     */
    private $array = [];

    /**
     * @param int         $type
     * @param string      $title
     * @param string|null $description
     * @param string|null $url
     */
    public function add(int $type, string $title, string $description = null, string $url = null)
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
    public function getHighestType(): int
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
    public function count(): int
    {
        return count($this->array);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        usort($this->array, function (NotificationEntry $a, NotificationEntry $b) {
            return $b->getType() <=> $a->getType();
        });

        return new ArrayIterator($this->array);
    }

    /**
     * Build default system notifications.
     *
     * @todo Remove translated messages
     * @return $this
     * @throws Exception
     */
    public function buildDefault(): self
    {
        $status = Status::getInstance();

        if ($status->hasPendingUpdates()) {
            $this->add(
                NotificationEntry::TYPE_DANGER, 
                'Systemupdate', 
                'Ein Datenbank-Update ist zwingend notwendig',
                'dbupdater.php'
            );
        }

        if (!$status->validFolderPermissions()) {
            $this->add(
                NotificationEntry::TYPE_DANGER, 
                'Dateisystem', 
                'Es sind Verzeichnisse nicht beschreibbar.',
                'permissioncheck.php'
            );
        }

        if ($status->hasInstallDir()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                'System',
                'Bitte löschen Sie das Installationsverzeichnis "/install/" im Shop-Wurzelverzeichnis.'
            );
        }

        if (!$status->validDatabaseStruct()) {
            $this->add(
                NotificationEntry::TYPE_DANGER,
                'Datenbank',
                'Es liegen Fehler in der Datenbankstruktur vor.',
                'dbcheck.php'
            );
        }

        if ($status->hasDifferentTemplateVersion()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                'Template',
                'Ihre Template-Version unterscheidet sich von Ihrer Shop-Version.<br />' .
                    'Weitere Hilfe zu Template-Updates finden Sie im <i class="fa fa-external-link"></i> Wiki',
                'shoptemplate.php'
            );
        }

        if ($status->hasMobileTemplateIssue()) {
            $this->add(
                NotificationEntry::TYPE_INFO,
                'Template',
                'Sie nutzen ein Full-Responsive-Template. ' .
                    'Die Aktivierung eines separaten Mobile-Templates ist in diesem Fall nicht notwendig.',
                'shoptemplate.php'
            );
        }

        if ($status->hasStandardTemplateIssue()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                'Template',
                'Sie haben kein Standard-Template aktiviert!',
                'shoptemplate.php'
            );
        }

        if ($status->hasActiveProfiler()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                'Plugin',
                'Der Profiler ist aktiv. Dies kann zu starken Leistungseinbußen im Shop führen.'
            );
        }

        if ($status->hasNewPluginVersions()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                'Plugin',
                'Es sind neue Plugin-Versionen vorhanden.',
                'pluginverwaltung.php'
            );
        }

        /* REMOTE CALL
        if (($subscription =  Shop()->RS()->getSubscription()) !== null) {
            if ((int)$subscription->bUpdate === 1) {
                if ((int)$subscription->nDayDiff <= 0) {
                    $this->add(
                        NotificationEntry::TYPE_WARNING,
                        'Subscription',
                        'Ihre Subscription ist abgelaufen. Jetzt erneuern.',
                        'https://jtl-url.de/subscription'
                    );
                } else {
                    $this->add(
                        NotificationEntry::TYPE_INFO,
                        'Subscription',
                        "Ihre Subscription läuft in {$subscription->nDayDiff} Tagen ab.",
                        'https://jtl-url.de/subscription'
                    );
                }
            }
        }
        */

        if ($status->hasInvalidPollCoupons()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                'Umfrage',
                'In einer Umfrage wird ein Kupon verwendet, welcher inaktiv ist oder nicht mehr existiert.'
            );
        }

        if ($status->hasFullTextIndexError()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                'Volltextsuche',
                'Der Volltextindex ist nicht vorhanden!',
                'sucheinstellungen.php'
            );
        }

        if ($status->hasInvalidPasswordResetMailTemplate()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                'E-Mail-Vorlage defekt',
                'Die E-Mail-Vorlage "Passwort Vergessen" ist veraltet.<br>' .
                    'Die Variable $neues_passwort ist nicht mehr verfügbar.<br>' .
                    'Bitte ersetzen Sie diese durch $passwordResetLink oder setzen Sie die Vorlage zurück.'
            );
        }

        if ($status->hasInsecureMailConfig()) {
            $this->add(
                NotificationEntry::TYPE_DANGER,
                'Unsichere SMTP-Verbindung',
                'Sie haben SMTP als Mail-Methode gewählt, allerdings keine Verschlüsselungsmethode ausgewählt.<br>' .
                'Wir empfehlen Ihnen dringend, Ihre Mail-Einstellungen anzupassen.<br>' .
                'Sie finden die Optionen unter "System &gt; E-Mails &gt; Emaileinstellungen &gt; SMTP Security".',
                Shop::getURL() . '/' . PFAD_ADMIN . 'einstellungen.php?kSektion=3'
            );
        }

        if ($status->needPasswordRehash2FA()) {
            $this->add(
                NotificationEntry::TYPE_DANGER,
                'Benutzerverwaltung',
                'Der Algorithmus zur Passwortspeicherung hat sich geändert.<br/>' .
                    'Bitte erzeugen Sie neue Notfall-Codes für die Zwei-Faktor-Authentifizierung.',
                'benutzerverwaltung.php'
            );
        }

        if (count($status->getDuplicateLinkGroupTemplateNames()) > 0) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                'Ungültige Linkgruppen',
                'Eine oder mehrere Linkgruppen nutzen nicht-eindeutige Template-Namen: ' .
                implode(', ', \Functional\pluck($status->getDuplicateLinkGroupTemplateNames(), 'cName')),
                'links.php'
            );
        }

        if ($status->hasDuplicateSpecialLinkTypes()) {
           $this->add(
               NotificationEntry::TYPE_DANGER,
               'Spezialseite mehrfach belegt',
               'Eine oder mehrere Spezialseiten sind mehrfach für die gleiche(n) Kundengruppe(n) angelegt',
               'links.php'
           );
        }

        return $this;
    }
}
