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
 * Class NewsList
 * @package News
 */
final class ItemList implements ItemListInterface
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
            "SELECT tnewssprache.languageID,
            tnewssprache.languageCode,
            tnews.cKundengruppe, 
            tnews.kNews, 
            tnewssprache.title AS localizedTitle, 
            tnewssprache.content, 
            tnewssprache.preview, 
            tnewssprache.previewImage, 
            tnewssprache.metaTitle, 
            tnewssprache.metaKeywords, 
            tnewssprache.metaDescription, 
            tnews.nAktiv AS isActive, 
            tnews.dErstellt AS dateCreated, 
            tnews.dGueltigVon AS dateValidFrom, 
            tseo.cSeo AS localizedURL
                FROM tnews
                JOIN tnewssprache
                    ON tnews.kNews = tnewssprache.kNews
                JOIN tseo 
                    ON tseo.cKey = 'kNews'
                    AND tseo.kKey = tnews.kNews
                WHERE tnews.kNews  IN (" . \implode(',', $this->itemIDs) . ")
                GROUP BY tnews.kNews, tnewssprache.languageID",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $items         = map(group($itemLanguages, function ($e) {
            return (int)$e->kNews;
        }), function ($e, $newsID) {
            $l = new Item($this->db);
            $l->setID($newsID);
            $l->map($e);

            return $l;
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
    public function addItem(ItemInterFace $item)
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