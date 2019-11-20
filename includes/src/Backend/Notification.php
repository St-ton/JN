<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Backend;

use ArrayIterator;
use Countable;
use Exception;
use IteratorAggregate;
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
            /** @var NotificationEntry $notify */
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
     * @todo Remove translated messages
     * @return $this
     * @throws Exception
     */
    public function buildDefault(): self
    {
        $status    = Status::getInstance();
        $db        = Shop::Container()->getDB();
        $cache     = Shop::Container()->getCache();
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

        if (!$status->validFolderPermissions()) {
            $this->add(
                NotificationEntry::TYPE_DANGER,
                __('validFolderPermissionsTitle'),
                __('validFolderPermissionsMessage'),
                'permissioncheck.php'
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

        if (!$status->validModifiedFileStruct() || !$status->validOrphanedFilesStruct()) {
            $this->add(
                NotificationEntry::TYPE_WARNING,
                __('validModifiedFileStructTitle'),
                __('validModifiedFileStructMessage'),
                'filecheck.php'
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
                __('hasInvalidPollCouponsTitle'),
                __('hasInvalidPollCouponsMessage')
            );
        }

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

        if ($status->hasInsecureMailConfig()) {
            $this->add(
                NotificationEntry::TYPE_DANGER,
                __('hasInsecureMailConfigTitle'),
                __('hasInsecureMailConfigMessage'),
                Shop::getURL() . '/' . \PFAD_ADMIN . 'einstellungen.php?kSektion=3'
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

        return $this;
    }
}
