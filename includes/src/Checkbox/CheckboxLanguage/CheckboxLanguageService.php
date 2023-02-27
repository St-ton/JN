<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxLanguage;

use JTL\Abstracts\AbstractService;
use JTL\DataObjects\AbstractDataObject;
use JTL\Interfaces\RepositoryInterface;

/**
 * Class CheckboxLanguageService
 * @package JTL\Checkbox\CheckboxLanguage
 */
class CheckboxLanguageService extends AbstractService
{
    /**
     * @param array $filters
     * @return array
     */
    public function getList(array $filters): array
    {
        $languageList      = [];
        $checkboxLanguages = $this->repository->getList($filters);
        foreach ($checkboxLanguages as $checkboxLanguage) {
            $language       = new CheckboxLanguageDataTableObject();
            $languageList[] = $language->hydrateWithObject($checkboxLanguage);
        }

        return $languageList;
    }

    /**
     * @param AbstractDataObject $updateDTO
     * @return bool
     */
    public function update(AbstractDataObject $updateDTO): bool
    {
        if (!$updateDTO instanceof CheckboxLanguageDataTableObject) {
            return false;
        }
        //need checkboxLanguageId, not provided by post
        $languageList = $this->getList([
            'kCheckBox' => $updateDTO->getCheckboxID(),
            'kSprache'  => $updateDTO->getLanguageID()
        ]);
        $language     = $languageList[0] ?? null;
        if ($language === null) {
            return $this->insert($updateDTO) > 0;
        }
        $updateDTO->setCheckboxLanguageID($language->getCheckboxLanguageID());

        return $this->repository->update($updateDTO);
    }

    protected function initRepository(): void
    {
        $this->repository = new CheckboxLanguageRepository();
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }
}
