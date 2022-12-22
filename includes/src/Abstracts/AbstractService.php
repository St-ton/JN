<?php

namespace JTL\Abstracts;

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
     * @inheritDoc
     */
    abstract public function getRepository(): RepositoryInterface;
}
