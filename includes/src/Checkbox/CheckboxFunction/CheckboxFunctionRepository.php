<?php declare(strict_types=1);

namespace JTL\Checkbox\CheckboxFunction;

use JTL\Abstracts\AbstractRepository;

/**
 * Class CheckboxFunctionRepository
 * @package JTL\Checkbox\CheckboxFunction
 */
class CheckboxFunctionRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'tcheckboxfunktion';
    }

    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'kCheckBoxFunktion';
    }

    /**
     * @inheritdoc
     */
    public function get(int $id): ?\stdClass
    {
        return $this->getDB()->getSingleObject(
            'SELECT *'
            . ' FROM ' . $this->getTableName()
            . ' WHERE ' . $this->getKeyName() . ' = :id',
            ['id' => $id]
        );
    }
}
