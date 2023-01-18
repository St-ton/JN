<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxLanguage;

use JTL\Abstracts\AbstractRepository;
use JTL\DataObjects\DataTableObjectInterface;

/**
 * Class CheckboxLanguageRepository
 * @package JTL\Checkbox\CheckboxLanguage
 */
class CheckboxLanguageRepository extends AbstractRepository
{
    protected string $tableName = 'tcheckboxsprache';

    protected string $keyName = 'kCheckBoxSprache';

    /**
     * @param DataTableObjectInterface $checkboxLanguage
     * @return int
     */
    public function insert(DataTableObjectInterface $checkboxLanguage): int
    {
        return $this->db->insertRow($this->getTableName(), $checkboxLanguage->toObject());
    }
}
