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
     * @param AbstractDataObject $checkboxLanguage
     * @return bool
     */
    public function update(AbstractDataObject $checkboxLanguage): bool
    {
        if (!$checkboxLanguage instanceof CheckboxLanguageDataTableObject) {
            return false;
        }
        //need checkboxLanguageId, not provided by post
        $languageList = $this->getList([
            'kCheckBox' => $checkboxLanguage->getCheckboxID(),
            'kSprache'  => $checkboxLanguage->getLanguageID()
        ]);
        $language     = $languageList[0] ?? null;
        if (\is_null($language)) {
            return $this->insert($checkboxLanguage) > 0;
        }
        $checkboxLanguage->setCheckboxLanguageID($language->getCheckboxLanguageID());

        return $this->repository->update($checkboxLanguage);
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository(): RepositoryInterface
    {
        if (\is_null($this->repository)) {
            $this->repository = new CheckboxLanguageRepository();
        }

        return $this->repository;
    }
}
