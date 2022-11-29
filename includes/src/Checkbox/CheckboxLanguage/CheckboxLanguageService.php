<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxLanguage;

/**
 * Class CheckboxLanguageService
 * @package JTL\Checkbox\CheckboxLanguage
 */
class CheckboxLanguageService
{
    /**
     * @param CheckboxLanguageRepository $repository
     */
    public function __construct(
        protected CheckboxLanguageRepository $repository
    ) {
    }

    /**
     * @param array $filter
     * @return array
     */
    public function getList(array $filter): array
    {
        $languageList      = [];
        $checkboxLanguages = $this->repository->getList($filter);
        foreach ($checkboxLanguages as $checkboxLanguage) {
            $language       = new CheckboxLanguageDataObject();
            $languageList[] = $language->hydrateWithObject($checkboxLanguage);
        }

        return $languageList;
    }

    /**
     * @param CheckboxLanguageDataObject $checkBoxLanguage
     * @return int
     */
    public function insert(CheckboxLanguageDataObject $checkBoxLanguage): int
    {
        return $this->repository->insert($checkBoxLanguage);
    }

    /**
     * @param CheckboxLanguageDataObject $checkboxLanguage
     * @return bool
     */
    public function update(CheckboxLanguageDataObject $checkboxLanguage): bool
    {
        //need checkboxLanguageId, not provided by post
        $language = $this->getList([
            'kCheckBox' => $checkboxLanguage->getCheckboxID(),
            'kSprache'  => $checkboxLanguage->getLanguageID()
        ])[0];
        $checkboxLanguage->setCheckboxLanguageID($language->getCheckboxLanguageID());

        return $this->repository->update($checkboxLanguage);
    }
}
