<?php

namespace JTL\Abstracts;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;
use JTL\Interfaces\RepositoryInterface;
use JTL\Interfaces\ServiceInterface;

abstract class AbstractService implements ServiceInterface
{
    /**
     * @param RepositoryInterface|null $repository
     */
    public function __construct(
        protected ?RepositoryInterface $repository = null
    ) {
        if (\is_null($this->repository)) {
            $this->getRepository();
        }
    }

    /**
     * @inheritdoc
     */
    abstract public function getRepository(): RepositoryInterface;

    /**
     * @param array $filters
     * @return array
     */
    public function getList(array $filters): array
    {
        return $this->repository->getList($filters);
    }

    /**
     * @param AbstractDataObject $insertDTO
     * @return int
     */
    public function insert(AbstractDataObject $insertDTO): int
    {
        if ($insertDTO instanceof DataTableObjectInterface) {
            return $this->repository->insert($insertDTO);
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
            return $this->repository->update($updateDTO);
        }

        return false;
    }
}
