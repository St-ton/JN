<?php

namespace JTL\Abstracts;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;
use JTL\Interfaces\RepositoryInterface;
use JTL\Interfaces\ServiceInterface;

/**
 * Class AbstractService
 * @package JTL\Abstracts
 */
abstract class AbstractService implements ServiceInterface
{
    public function __construct()
    {
        $this->initDependencies();
    }

    /**
     * @inheritdoc
     */
    abstract public function getRepository(): RepositoryInterface;

    /**
     * @return void
     */
    abstract protected function initDependencies(): void;

    /**
     * @param array $filters
     * @return array
     */
    public function getList(array $filters): array
    {
        return $this->getRepository()->getList($filters);
    }

    /**
     * @param AbstractDataObject $insertDTO
     * @return int
     */
    public function insert(AbstractDataObject $insertDTO): int
    {
        if ($insertDTO instanceof DataTableObjectInterface) {
            return $this->getRepository()->insert($insertDTO);
        }

        return 0;
    }

    /**
     * @param AbstractDataObject $updateDTO
     * @return bool
     */
    public function update(AbstractDataObject $updateDTO): bool
    {
        if (($updateDTO instanceof DataTableObjectInterface) && $updateDTO->getID() > 0) {
            return $this->getRepository()->update($updateDTO);
        }

        return false;
    }
}
