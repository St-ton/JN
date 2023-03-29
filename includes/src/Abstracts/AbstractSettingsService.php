<?php declare(strict_types=1);

namespace JTL\Abstracts;

use JTL\DataObjects\AbstractDataObject;
use JTL\DataObjects\DataTableObjectInterface;
use JTL\Interfaces\ServiceInterface;
use JTL\Interfaces\SettingsRepositoryInterface;

/**
 * Class AbstractService
 * @package JTL\Abstracts
 */
abstract class AbstractSettingsService implements ServiceInterface
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected SettingsRepositoryInterface $repository;

    public function __construct()
    {
        $this->initDependencies();
    }

    /**
     * @inheritdoc
     */
    abstract public function getRepository(): SettingsRepositoryInterface;

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
