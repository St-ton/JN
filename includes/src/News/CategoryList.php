<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News;


use DB\DbInterface;
use DB\ReturnType;
use Tightenco\Collect\Support\Collection;
use function Functional\group;
use function Functional\map;

/**
 * Class CategoryList
 * @package News
 */
final class CategoryList implements ItemListInterface
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var int[]
     */
    private $itemIDs;

    /**
     * @var Collection
     */
    private $items;

    /**
     * LinkList constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db    = $db;
        $this->items = new Collection();
    }

    /**
     * @inheritdoc
     */
    public function createItems(array $itemIDs): Collection
    {
        $this->itemIDs = \array_map('\intval', $itemIDs);
        if (\count($this->itemIDs) === 0) {
            return $this->items;
        }
        $itemLanguages = $this->db->query(
            'SELECT *
                FROM tnewskategorie
                WHERE kNewsKategorie  IN (' . \implode(',', $this->itemIDs) . ')',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $items         = map(group($itemLanguages, function ($e) {
            return (int)$e->kNewsKategorie;
        }), function ($e, $newsID) {
            $c = new Category($this->db);
            $c->setID($newsID);
            $c->map($e);

            return $c;
        });
        foreach ($items as $item) {
            $this->items->push($item);
        }

        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function setItems(Collection $items)
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function addItem($item)
    {
        $this->items->push($item);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res       = \get_object_vars($this);
        $res['db'] = '*truncated*';

        return $res;
    }
}