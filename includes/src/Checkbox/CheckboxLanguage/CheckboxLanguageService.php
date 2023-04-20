<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxLanguage;

use JTL\Abstracts\AbstractService;
use JTL\DataObjects\AbstractDataObject;

/**
 * Class CheckboxLanguageService
 * @package JTL\Checkbox\CheckboxLanguage
 */
class CheckboxLanguageService extends AbstractService
{
    /**
     * @var CheckboxLanguageRepository
     */
    private CheckboxLanguageRepository $repository;

    /**
     * @param array $filters
     * @return array
     */
    public function getList(array $filters): array
    {
        $languageList      = [];
        $checkboxLanguages = $this->getRepository()->getList($filters);
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

        return $this->getRepository()->update($updateDTO);
    }

    /**
     * @return void
     */
    protected function initDependencies(): void
    {
        $this->repository = new CheckboxLanguageRepository();
    }

    /**
     * @return CheckboxLanguageRepository
     */
    public function getRepository(): CheckboxLanguageRepository
    {
        return $this->repository;
    }
}
