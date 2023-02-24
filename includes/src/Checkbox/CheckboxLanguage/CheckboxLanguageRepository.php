<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxLanguage;

use JTL\Abstracts\AbstractRepository;

/**
 * Class CheckboxLanguageRepository
 * @package JTL\Checkbox\CheckboxLanguage
 */
class CheckboxLanguageRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'tcheckboxsprache';
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'kCheckBoxSprache';
    }
}
