<?php declare(strict_types=1);

namespace JTL\CheckBox;

use JTL\CheckBox\CheckBoxDataObject;
use JTL\CheckBox\CheckBoxRepository;

use JTL\Shop;

/**
 * Class CheckBox
 * @package JTL
 */
class CheckBoxService
{
    protected CheckBoxRepository $repository;

    /**
     * @param CheckBoxRepository $repository
     */
    public function __construct(CheckBoxRepository $repository)
    {
        $this->repository = $repository;
    }


    public function insertCheckbox(CheckBoxDataObject $checkBox): int
    {
        return $this->repository->insert($checkBox);
    }

    /**
     * @param CheckBoxDataObject $checkBox
     * @return bool
     */
    public function saveCheckBox(CheckBoxDataObject $checkBox): bool
    {
        if ($checkBox->kCheckBox > 0) {
            return $this->repository->update($checkBox);
        }

        return false;
    }

    /**
     * @param int[] $checkBoxIDs
     * @return bool
     */
    public function activate(array $checkBoxIDs): bool
    {
        $res = $this->repository->activate($checkBoxIDs);
        Shop::Container()->getCache()->flushTags(['checkbox']);

        return $res;
    }

    /**
     * @param int[] $checkBoxIDs
     * @return bool
     */
    public function deactivate(array $checkBoxIDs): bool
    {
        $res = $this->repository->deactivate($checkBoxIDs);
        Shop::Container()->getCache()->flushTags(['checkbox']);

        return $res;
    }
}
