<?php declare(strict_types=1);

namespace JTL\CheckBox\CheckBoxLanguage;

use JTL\CheckBox\CheckBoxLanguage\CheckBoxLanguageDataObject;
use JTL\CheckBox\CheckBoxLanguage\CheckBoxLanguageRepository;

use JTL\Shop;

/**
 * Class CheckBox
 * @package JTL
 */
class CheckBoxLanguageService
{
    protected CheckBoxLanguageRepository $repository;

    /**
     * @param CheckBoxLanguageRepository $repository
     */
    public function __construct(CheckBoxLanguageRepository $repository)
    {
        $this->repository = $repository;
    }


    public function insert(CheckBoxLanguageDataObject $checkBoxLanguage): int
    {
        return $this->repository->insert($checkBoxLanguage);
    }

    /**
     * @param CheckBoxLanguageDataObject $checkBoxLanguage
     * @return bool
     */
    public function update(CheckBoxLanguageDataObject $checkBoxLanguage): bool
    {
            return $this->repository->update($checkBoxLanguage);
    }
}