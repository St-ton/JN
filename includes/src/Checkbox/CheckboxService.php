<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\Abstracts\AbstractService;

/**
 * Class CheckboxService
 * @package JTL\Checkbox
 */
class CheckboxService extends AbstractService
{
    /**
     * @var CheckboxRepository
     */
    private CheckboxRepository $repository;

    /**
     * @param int $id
     * @return CheckboxDataTableObject
     */
    public function get(int $id): CheckboxDataTableObject
    {
        return (new CheckboxDataTableObject())->hydrateWithObject($this->getRepository()->get($id));
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function activate(array $checkboxIDs): bool
    {
        return $this->getRepository()->activate($checkboxIDs);
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function deactivate(array $checkboxIDs): bool
    {
        return $this->getRepository()->deactivate($checkboxIDs);
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
    protected function initDependencies(): void
    {
        $this->repository = new CheckboxRepository();
    }

    /**
     * @return CheckboxRepository
     */
    public function getRepository(): CheckboxRepository
    {
        return $this->repository;
    }
}
