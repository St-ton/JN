<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

namespace OPC;

/**
 * Class PageLocker
 * @package OPC
 */
class PageLocker
{
    /**
     * @var PageModel
     */
    protected $model;

    /**
     * PageLocker constructor.
     * @param PageModel $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Lock this page to only be manipulated by this one user
     *
     * @param $userName
     * @return bool if page lock could be granted
     * @throws \Exception
     */
    public function lock($userName)
    {
        if ($userName === '') {
            throw new \Exception('Name of the user that locks this page is empty.');
        }

        $lockedBy = $this->model->getLockedBy();
        $lockedAt = $this->model->getLockedAt();

        if ($lockedBy !== '' && $lockedBy !== $userName && strtotime($lockedAt) + 60 > time()) {
            return false;
        }

        $this->model
            ->setLockedBy($userName)
            ->setLockedAt(date('Y-m-d H:i:s'))
            ->saveLockStatus();

        return true;
    }

    /**
     * Unlock this page if it was locked
     * @throws \Exception
     */
    public function unlock()
    {
        $this->model
            ->setLockedBy('')
            ->saveLockStatus();
    }
}
