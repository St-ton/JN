<?php

namespace JTL\Backend;

use ArrayIterator;
use Countable;
use Exception;
use Illuminate\Support\Collection;
use IteratorAggregate;
use JTL\DB\ReturnType;
use JTL\IO\IOResponse;
use JTL\Link\Admin\LinkAdmin;
use JTL\Shop;
use JTL\SingletonTrait;
use function Functional\pluck;

/**
 * Class Notification
 * @package JTL\Backend
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
     * @param string|null $hash
     */
    public function add(
        int $type,
        string $title,
        ?string $description = null,
        ?string $url = null,
        ?string $hash = null
    ): void {
        $this->addNotify(new NotificationEntry($type, $title, $description, $url, $hash));
    }

    /**
     * @param NotificationEntry $notify
     */
    public function addNotify(NotificationEntry $notify): void
    {
        $this->array[] = $notify;
    }

    /**
     * @param bool $withIgnored
     * @return int - highest type in record
     */
    public function getHighestType(bool $withIgnored = false): int
    {
        $type = NotificationEntry::TYPE_NONE;
        foreach ($this as $notify) {
            /** @var NotificationEntry $notify */
            if (($withIgnored || !$notify->isIgnored()) && $notify->getType() > $type) {
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
        return \count(\array_filter($this->array, static function ($item) {
            return !$item->isIgnored();
        }));
    }

    /**
     * @return int
     */
    public function totalCount(): int
    {
        return \count($this->array);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        \usort($this->array, static function (NotificationEntry $a, NotificationEntry $b) {
            return $b->getType() <=> $a->getType();
        });

        return new ArrayIterator($this->array);
    }

    /**
     * Build default system notifications.
     *
     * @param bool $flushCache
     * @return $this
     * @throws Exception
     * @todo Remove translated messages
     */
    public function buildDefault(bool $flushCache = false): self
    {
        $db        = Shop::Container()->getDB();
        $cache     = Shop::Container()->getCache();
        $status    = Status::getInstance($db, $cache, $flushCache);
        $linkAdmin = new LinkAdmin($db, $cache);

        Shop::Container()->getGetText()->loadAdminLocale('notifications');

        if ($status->hasPendingUpdates()) {
            $this->add(
                NotificationEntry::TYPE_DANGER,
                __('hasPendingUpdatesTitle'),
                __('hasPendingUpdatesMessage'),
                'dbupdater.php'
            );
            return $this;
        }

        $hash = 'validFolderPermissions';
        if (!$status->validFolderPermissions($hash)) {
            $this->add(
                NotificationEntry::TYPE_DANGER,
                __('validFolderPermissionsTitle'),
                __('validFolderPermissionsMessage'),
                'permissioncheck.php',
                $hash
            );
        }

        if ($status->hasInstallDir()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                __('hasInstallDirTitle'),
                __('hasInstallDirMessage')
            );
        }

        if (!$status->validDatabaseStruct()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                __('validDatabaseStructTitle'),
                __('validDatabaseStructMessage'),
                'dbcheck.php'
            );
        }

        $hash = 'validModifiedFileStruct';
        if (!$status->validModifiedFileStruct($hash) || !$status->validOrphanedFilesStruct($hash)) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                __('validModifiedFileStructTitle'),
                __('validModifiedFileStructMessage'),
                'filecheck.php',
                $hash
            );
        }

        if ($status->hasMobileTemplateIssue()) {
            $this->add(
                NotificationEntry::TYPE_INFO,
                __('hasMobileTemplateIssueTitle'),
                __('hasMobileTemplateIssueMessage'),
                'shoptemplate.php'
            );
        }

        if ($status->hasStandardTemplateIssue()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                __('hasStandardTemplateIssueTitle'),
                __('hasStandardTemplateIssueMessage'),
                'shoptemplate.php'
            );
        }

        if ($status->hasActiveProfiler()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                __('hasActiveProfilerTitle'),
                __('hasActiveProfilerMessage')
            );
        }

        if ($status->hasNewPluginVersions()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                __('hasNewPluginVersionsTitle'),
                __('hasNewPluginVersionsMessage'),
                'pluginverwaltung.php'
            );
        }

        $hash = 'hasLicenseExpirations';
        if ($status->hasLicenseExpirations($hash)) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                __('hasLicenseExpirationsTitle'),
                __('hasLicenseExpirationsMessage'),
                'licenses.php',
                $hash
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

        if ($status->hasFullTextIndexError()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                __('hasFullTextIndexErrorTitle'),
                __('hasFullTextIndexErrorMessage'),
                'sucheinstellungen.php'
            );
        }

        if ($status->hasInvalidPasswordResetMailTemplate()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                __('hasInvalidPasswordResetMailTemplateTitle'),
                __('hasInvalidPasswordResetMailTemplateMessage')
            );
        }

        $hash = 'hasInsecureMailConfig';
        if ($status->hasInsecureMailConfig($hash)) {
            $this->add(
                NotificationEntry::TYPE_DANGER,
                __('hasInsecureMailConfigTitle'),
                __('hasInsecureMailConfigMessage'),
                Shop::getURL() . '/' . \PFAD_ADMIN . 'einstellungen.php?kSektion=3',
                $hash
            );
        }

        try {
            if ($status->needPasswordRehash2FA()) {
                $this->add(
                    NotificationEntry::TYPE_DANGER,
                    __('needPasswordRehash2FATryTitle'),
                    __('needPasswordRehash2FATryMessage'),
                    'benutzerverwaltung.php'
                );
            }
        } catch (Exception $e) {
            $this->add(
                NotificationEntry::TYPE_DANGER,
                __('needPasswordRehash2FACatchTitle'),
                __('needPasswordRehash2FACatchMessage'),
                'dbupdater.php'
            );
        }

        if (\count($status->getDuplicateLinkGroupTemplateNames()) > 0) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                __('getDuplicateLinkGroupTemplateNamesTitle'),
                \sprintf(
                    __('getDuplicateLinkGroupTemplateNamesMessage'),
                    \implode(', ', pluck($status->getDuplicateLinkGroupTemplateNames(), 'cName'))
                ),
                'links.php'
            );
        }

        if ($linkAdmin->getDuplicateSpecialLinks()->count() > 0) {
            $this->add(
                NotificationEntry::TYPE_DANGER,
                __('duplicateSpecialLinkTitle'),
                __('duplicateSpecialLinkDesc'),
                'links.php'
            );
        }

        if (($exportSyntaxErrorCount = $status->getExportFormatErrorCount()) > 0) {
            $this->add(
                NotificationEntry::TYPE_DANGER,
                __('getExportFormatErrorCountTitle'),
                \sprintf(__('getExportFormatErrorCountMessage'), $exportSyntaxErrorCount),
                'exportformate.php'
            );
        }

        if (($emailSyntaxErrorCount = $status->getEmailTemplateSyntaxErrorCount()) > 0) {
            $this->add(
                NotificationEntry::TYPE_DANGER,
                __('getEmailTemplateSyntaxErrorCountTitle'),
                \sprintf(__('getEmailTemplateSyntaxErrorCountMessage'), $emailSyntaxErrorCount),
                'emailvorlagen.php'
            );
        }

        if (!$status->hasExtensionSOAP()) {
            $this->add(
                NotificationEntry::TYPE_INFO,
                __('ustIdMiasCheckTitle'),
                __('ustIdMiasCheckMessage'),
                Shop::getAdminURL().'/einstellungen.php?kSektion=6'
            );
        }

        return $this;
    }

    /**
     * @param IOResponse $response
     * @param string     $hash
     * @return void
     * @throws Exception
     */
    protected function ignoreNotification(IOResponse $response, string $hash): void
    {
        Shop::Container()->getDB()->upsert('tnotificationsignore', (object)[
            'user_id'           => Shop::Container()->getAdminAccount()->getID(),
            'notification_hash' => $hash,
            'created'           => 'NOW()',
        ], ['created']);

        $this->updateNotifications($response);
    }

    /**
     * @param IOResponse $response
     * @return void
     * @throws Exception
     */
    protected function resetIgnoredNotifications(IOResponse $response): void
    {
        Shop::Container()->getDB()->delete(
            'tnotificationsignore',
            'user_id',
            Shop::Container()->getAdminAccount()->getID()
        );

        $this->updateNotifications($response, true);
    }

    /**
     * @param IOResponse $response
     * @param bool       $flushCache
     * @return void
     * @throws Exception
     */
    protected function updateNotifications(IOResponse $response, bool $flushCache = false): void
    {
        Shop::fire('backend.notification', $this->buildDefault($flushCache));
        $db = Shop::Container()->getDB();
        /** @var Collection $res */
        $res = $db->queryPrepared(
            'SELECT notification_hash
                FROM tnotificationsignore
                WHERE user_id = :userID', // AND NOW() < DATE_ADD(created, INTERVAL 7 DAY)',
            [
                'userID' => Shop::Container()->getAdminAccount()->getID(),
            ],
            ReturnType::COLLECTION
        );

        $hashes = $res->keyBy('notification_hash');
        foreach ($this->array as $notificationEntry) {
            if (($hash = $notificationEntry->getHash()) !== null && $hashes->has($hash)) {
                $notificationEntry->setIgnored(true);
                $hashes->forget($hash);
            }
        }
        if ($hashes->count() > 0) {
            $db->query(
                "DELETE FROM tnotificationsignore
                    WHERE notification_hash IN ('" . $hashes->implode('notification_hash', "', '") . "')",
                ReturnType::DEFAULT
            );
        }

        $response->assignDom('notify-drop', 'innerHTML', \getNotifyDropIO()['tpl']);
    }

    /**
     * @param string     $action
     * @param mixed|null $data
     * @return IOResponse
     * @throws Exception
     */
    public static function ioNotification(string $action, $data = null): IOResponse
    {
        $response      = new IOResponse();
        $notifications = self::getInstance();

        switch ($action) {
            case 'update':
                $notifications->updateNotifications($response);
                break;
            case 'refresh':
                $notifications->updateNotifications($response, true);
                break;
            case 'dismiss':
                $notifications->ignoreNotification($response, (string)$data);
                break;
            case 'reset':
                $notifications->resetIgnoredNotifications($response);
                break;
        }

        return $response;
    }
}
