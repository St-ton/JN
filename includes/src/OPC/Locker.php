<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class Locker
 * @package OPC
 */
class Locker
{
    /**
     * @var null|PageDB
     */
    protected $pageDB = null;

    /**
     * Locker constructor.
     * @param PageDB $pageDB
     */
    public function __construct(PageDB $pageDB)
    {
        $this->pageDB = $pageDB;
    }

    /**
     * Try to lock draft to only be manipulated by this one user
     *
     * @param string $userName
     * @param Page $page
     * @return bool
     * @throws \Exception
     */
    public function lock(string $userName, Page $page)
    {
        if ($userName === '') {
            throw new \Exception('Name of the user that locks this page is empty.');
        }

        $lockedBy = $page->getLockedBy();
        $lockedAt = $page->getLockedAt();

        if ($lockedBy !== '' && $lockedBy !== $userName && strtotime($lockedAt) + 60 > time()) {
            return false;
        }

        $page
            ->setLockedBy($userName)
            ->setLockedAt(date('Y-m-d H:i:s'));

        $this->pageDB->saveDraftLockStatus($page);

        return true;
    }

    /**
     * Unlock this draft if it was locked
     *
     * @param Page $page
     * @throws \Exception
     */
    public function unlock(Page $page)
    {
        $page->setLockedBy('');
        $this->pageDB->saveDraftLockStatus($page);
    }
}
