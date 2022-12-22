<?php

namespace JTL\Abstracts;

use JTL\Interfaces\RepositoryInterface;
use JTL\Interfaces\ServiceInterface;

abstract class AbstractService implements ServiceInterface
{
    /**
     * @param RepositoryInterface $repository
     */
    public function __construct(
        protected RepositoryInterface $repository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
