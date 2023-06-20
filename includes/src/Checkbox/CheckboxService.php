<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\Abstracts\AbstractService;
use JTL\Interfaces\RepositoryInterface;

/**
 * Class CheckboxService
 * @package JTL\Checkbox
 */
class CheckboxService extends AbstractService
{
    /**
     * @param int $id
     * @return ?CheckboxDataTableObject
     */
    public function get(int $id): ?CheckboxDataTableObject
    {
        $data = $this->repository->get($id);
        if ($data === null) {
            return null;
        }

        return (new CheckboxDataTableObject())->hydrateWithObject($data);
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function activate(array $checkboxIDs): bool
    {
        if ($this->repository instanceof CheckboxRepository) {
            return $this->repository->activate($checkboxIDs);
        }

        return false;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function deactivate(array $checkboxIDs): bool
    {
        if ($this->repository instanceof CheckboxRepository) {
            return $this->repository->deactivate($checkboxIDs);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    protected function initRepository(): void
    {
        $this->repository = new CheckboxRepository();
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
