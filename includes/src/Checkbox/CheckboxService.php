<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\Abstracts\AbstractService;
use JTL\Interfaces\RepositoryInterface;
use JTL\Shop;

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
        $res = false;
        if ($this->repository instanceof CheckboxRepository) {
            $res = $this->repository->activate($checkboxIDs);
            Shop::Container()->getCache()->flushTags(['checkbox']);
        }

        return $res;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function deactivate(array $checkboxIDs): bool
    {
        $res = false;
        if ($this->repository instanceof CheckboxRepository) {
            $res = $this->repository->deactivate($checkboxIDs);
            Shop::Container()->getCache()->flushTags(['checkbox']);
        }
        return $res;
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository(): RepositoryInterface
    {
        if (\is_null($this->repository)) {
            $this->repository = new CheckboxRepository();
        }

        return $this->repository;
    }
}
