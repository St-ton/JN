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
                FROM tnewskategoriesprache
                JOIN tnewskategorie
                    ON tnewskategoriesprache.kNewsKategorie = tnewskategorie.kNewsKategorie
                JOIN tseo
                    ON tseo.cKey = \'kNewsKategorie\'
                    AND tseo.kKey = tnewskategorie.kNewsKategorie
                WHERE tnewskategorie.kNewsKategorie  IN (' . \implode(',', $this->itemIDs) . ')
                GROUP BY tnewskategoriesprache.kNewsKategorie,tnewskategoriesprache.languageID
                ORDER BY tnewskategorie.lft',
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
     * @param Collection $tree
     * @param int        $id
     * @return Category|null
     */
    private function findParentCategory(Collection $tree, int $id)
    {
        $found = $tree->first(function (Category $e) use ($id) {
            return $e->getID() === $id;
        });
        if ($found !== null) {
            return $found;
        }
        foreach ($tree as $item) {
            $found = $this->findParentCategory($item->getChildren(), $id);

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * @return Collection
     */
    public function generateTree(): Collection
    {
        $tree = new Collection();
        foreach ($this->items as $item) {
            /** @var Category $item */
            if ($item->getParentID() === 0) {
                $tree->push($item);
                continue;
            }
            $parentID = $item->getParentID();
            $found    = $this->findParentCategory($tree, $parentID);

            if ($found !== null) {
                $found->addChild($item);
            } else {
                echo '<br>nothing found for ' . $parentID;
                \Shop::dbg($tree, true, 'Tree:');
            }
        }

        return $tree;
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