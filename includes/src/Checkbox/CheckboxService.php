<?php declare(strict_types=1);

namespace JTL\Checkbox;

use JTL\Abstracts\AbstractService;
use JTL\CheckBox;

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

    /**
     * @param CheckboxValidationDataObject $data
     * @param array                        $post
     * @return array
     */
    public function validateCheckBox(CheckboxValidationDataObject $data, array $post): array
    {
        $checks = [];
        foreach ($this->getCheckBoxValidationData($data) as $checkBox) {
            if ($checkBox->nPflicht === 1 && !isset($post[$checkBox->cID])) {
                if ($checkBox->cName === CheckBox::CHECKBOX_DOWNLOAD_ORDER_COMPLETE
                    && $data->getHasDownloads() === false) {
                    continue;
                }
                $checks[$checkBox->cID] = 1;
            }
        }

        return $checks;
    }

    /**
     * @param CheckboxValidationDataObject $data
     * @return CheckBox[]
     */
    public function getCheckBoxValidationData(
        CheckboxValidationDataObject $data
    ): array {
        $checkboxes = $this->repository->getCheckBoxValidationData(
            $data
        );
        \executeHook(\HOOK_CHECKBOX_CLASS_GETCHECKBOXFRONTEND, [
            'oCheckBox_arr' => &$checkboxes,
            'nAnzeigeOrt'   => $data->getLocation(),
            'kKundengruppe' => $data->getCustomerGroupId(),
            'bAktiv'        => $data->getActive(),
            'bSprache'      => $data->getLanguage(),
            'bSpecial'      => $data->getSpecial(),
            'bLogging'      => $data->getLogging(),
        ]);

        return $checkboxes;
    }
}
