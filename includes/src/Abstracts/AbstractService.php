<?php

namespace JTL\Abstracts;

use JTL\DataObjects\DataObjectInterface;
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
    abstract public function getList(array $filters): array;

    /**
     * @param DataTableObjectInterface|DataObjectInterface $object
     * @return int
     */
    abstract public function insert(DataTableObjectInterface|DataObjectInterface $object): int;

    /**
     * @param DataTableObjectInterface|DataObjectInterface $object
     * @return bool
     */
    abstract public function update(DataTableObjectInterface|DataObjectInterface $object): bool;
}
