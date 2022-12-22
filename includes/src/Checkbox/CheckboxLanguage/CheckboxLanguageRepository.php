<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxLanguage;

use JTL\Abstracts\AbstractRepository;
use JTL\DataObjects\DataObjectInterface;
use JTL\DB\DbInterface;
use JTL\Shop;

/**
 * Class CheckboxLanguageRepository
 * @package JTL\Checkbox\CheckboxLanguage
 */
class CheckboxLanguageRepository extends AbstractRepository
{
    protected string $tableName = 'tcheckboxsprache';

    protected string $keyName = 'kCheckBoxSprache';

    /**
     * @param CheckboxLanguageDataObject $checkboxLanguage
     * @return int
     */
    public function insert(DataObjectInterface $checkboxLanguage): int
    {
        return $this->db->insertRow($this->getTableName(), $checkboxLanguage->toObject());
    }

    public function delete(int $id): bool
    {
        // TODO: Implement delete() method.
        return false;
    }
}
