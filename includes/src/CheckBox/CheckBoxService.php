<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\Checkbox\CheckboxDataObject;
use JTL\Checkbox\CheckboxRepository;

use JTL\Shop;
use PhpParser\Node\Expr\Cast\Object_;

/**
 * Class Checkbox
 * @package JTL
 */
class CheckboxService
{
    /**
     * @var CheckboxRepository
     */
    protected CheckboxRepository $repository;

    /**
     * @param CheckboxRepository $repository
     */
    public function __construct(CheckboxRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $id
     * @return CheckboxDataObject
     */
    public function get(int $id): CheckboxDataObject
    {
        return (new CheckboxDataObject())->hydrate($this->repository->get($id));
    }

    /**
     * @param int $checkBoxFunctionID
     * @return object
     */
    public function getCheckBoxFunction(int $checkBoxFunctionID): object
    {
        return $this->repository->getCheckBoxFunction($checkBoxFunctionID);
    }

    /**
     * @param  CheckboxDataObject $checkbox
     * @return int
     */
    public function insertCheckbox(CheckboxDataObject $checkbox): int
    {
        return $this->repository->insert($checkbox);
    }

    /**
     * @param CheckboxDataObject $checkbox
     * @return bool
     */
    public function update(CheckboxDataObject $checkbox): bool
    {
        if ($checkbox->kCheckBox > 0) {
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
}
