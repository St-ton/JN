<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\Abstracts\AbstractService;
use JTL\DataObjects\DataObjectInterface;
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
     * @return CheckboxDataObject
     */
    public function get(int $id): CheckboxDataObject
    {
        return (new CheckboxDataObject())->hydrateWithObject($this->repository->get($id));
    }

    /**
     * @param  CheckboxDataObject $checkbox
     * @return int
     */
    public function insertCheckbox(DataObjectInterface $checkbox): int
    {
        return $this->repository->insert($checkbox);
    }

    /**
     * @param CheckboxDataObject $checkbox
     * @return bool
     */
    public function update(DataObjectInterface $checkbox): bool
    {
        if ($checkbox->getCheckboxID() > 0) {
            return $this->repository->update($checkbox);
        }

        return false;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function activate(array $checkboxIDs): bool
    {
        $res = $this->repository->activate($checkboxIDs);
        Shop::Container()->getCache()->flushTags(['checkbox']);

        return $res;
    }

    /**
     * @param int[] $checkboxIDs
     * @return bool
     */
    public function deactivate(array $checkboxIDs): bool
    {
        $res = $this->repository->deactivate($checkboxIDs);
        Shop::Container()->getCache()->flushTags(['checkbox']);

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
