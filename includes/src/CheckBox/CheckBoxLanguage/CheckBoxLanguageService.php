<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxLanguage;

use JTL\CheckBox\CheckBoxLanguage\CheckboxLanguageDataObject;
use JTL\CheckBox\CheckBoxLanguage\CheckboxLanguageRepository;

/**
 * Class Checkbox
 * @package JTL
 */
class CheckboxLanguageService
{
    /**
     * @var CheckboxLanguageRepository
     */
    protected CheckboxLanguageRepository $repository;

    /**
     * @param CheckboxLanguageRepository $repository
     */
    public function __construct(CheckboxLanguageRepository $repository)
    {
        $this->repository = $repository;
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
        //need checkBoxLanguageId, not provided by post
        $checkboxLanguage->checkboxLanguageID = $this->getList([
            'kCheckBox' => $checkboxLanguage->checkboxID,
            'kSprache'  => $checkboxLanguage->languageID])[0]
            ->checkboxLanguageID;

        return $this->repository->update($checkboxLanguage);
    }
}
