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
     * @param AbstractDataObject $object
     * @return int
     */
    public function insert(AbstractDataObject $object): int
    {
        if ($object instanceof DataTableObjectInterface) {
            return $this->repository->insert($object);
        }

        return 0;
    }

    /**
     * @param AbstractDataObject $object
     * @return bool
     */
    public function update(AbstractDataObject $object): bool
    {
        if ($object instanceof DataTableObjectInterface) {
            if ($object->getID() > 0) {
                return $this->repository->update($object);
            }
        }

        return false;
    }
}
