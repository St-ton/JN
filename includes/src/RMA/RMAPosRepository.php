<?php declare(strict_types=1);

namespace JTL\RMA;

use Illuminate\Support\Collection;
use JTL\Abstracts\AbstractRepository;
use JTL\Catalog\Product\Preise;
use JTL\Shop;
use stdClass;

/**
 * Class RMARepository
 * @package JTL\RMA
 */
class RMAPosRepository extends AbstractRepository
{
    /**
     * @param int $id
     * @return stdClass|null
     */
    public function get(int $id): ?stdClass
    {
        return $this->getDB()->getSingleObject(
            "SELECT *, DATE_FORMAT(dErstellt, '%d.%m.%Y %H:%i:%s') AS dErstellt_DE"
            . ' FROM ' . $this->getTableName()
            . ' WHERE ' . $this->getKeyName() . ' = :cbid',
            ['cbid' => $id]
        );
    }
    
    /**
     * @param array $filters
     * @return array
     * @since 5.3.0
     */
    public function getList(array $filters): array
    {
        $filters = ['rmaID' => $filters['rmaID'] ?? 0];
        $result  = Shop::Container()->getDB()->getObjects(
            'SELECT ' . $this->getTableName() . '.*, twarenkorbpos.kWarenkorb, tbestellung.cBestellNr
            FROM ' . $this->getTableName() . '
            LEFT JOIN twarenkorbpos
                ON ' . $this->getTableName() . '.orderPosID = twarenkorbpos.kBestellpos
                AND ' . $this->getTableName() . '.rmaID = twarenkorbpos.kWarenkorb
            LEFT JOIN tbestellung
                ON tbestellung.kWarenkorb = twarenkorbpos.kWarenkorb
            WHERE ' . $this->getTableName() . '.kRetoure = :rmaID',
            $filters
        );
        foreach ($result as &$obj) {
            $obj->unitPriceNet = Preise::getLocalizedPriceString($obj->unitPriceNet);
        }
        return $result;
    }
    
    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'rmapos';
    }
    
    /**
     * @return string
     */
    public function getKeyName(): string
    {
        return 'id';
    }
}
