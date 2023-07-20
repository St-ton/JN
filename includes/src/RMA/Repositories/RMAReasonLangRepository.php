<?php declare(strict_types=1);

namespace JTL\RMA\Repositories;

use JTL\Abstracts\AbstractRepositoryTim;

/**
 * Class RMAReasonLangRepository
 * @package JTL\RMA
 */
class RMAReasonLangRepository extends AbstractRepositoryTim
{

    /**
     * @return array
     */
    public function getColumnMapping(): array
    {
        return [
            'id'       => 'id',
            'reasonID' => 'reasonID',
            'langID'   => 'langID',
            'title'    => 'title'
        ];
    }

    /**
     * @param array $data
     * @return array
     */
    public function getDefaultValues(array $data = []): array
    {
        $default = [
            'id'     => 0,
            'reasonID' => 0,
            'langID' => 0,
            'title'  => ''
        ];
        return $this->arrayCombine($default, $data);
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'rma_reasons_lang';
    }
    
    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }
}
