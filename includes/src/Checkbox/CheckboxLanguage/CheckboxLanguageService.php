<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxLanguage;

use JTL\Abstracts\AbstractRepository;
use JTL\Abstracts\AbstractService;
use JTL\Interfaces\RepositoryInterface;

/**
 * Class CheckboxLanguageService
 * @package JTL\Checkbox\CheckboxLanguage
 */
class CheckboxLanguageService extends AbstractService
{
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

    public function getRepository(): RepositoryInterface
    {
        if (\is_null($this->repository)) {
            $this->repository = new CheckboxLanguageRepository();
        }

        return $this->repository;
    }
}
