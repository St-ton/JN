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
     * @return CheckboxDataTableObject
     */
    public function get(int $id): CheckboxDataTableObject
    {
        return (new CheckboxDataTableObject())->hydrateWithObject($this->repository->get($id));
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
     * @param array $checkboxIDs
     * @return bool
     */
    public function delete(array $checkboxIDs): bool
    {
        return $this->getRepository()->delete($checkboxIDs);
    }

    /**
     * @return void
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
