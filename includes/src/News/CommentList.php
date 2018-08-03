<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace News;


use DB\DbInterface;
use DB\ReturnType;
use Tightenco\Collect\Support\Collection;
use function Functional\first;
use function Functional\group;
use function Functional\map;

/**
 * Class CommentList
 * @package News
 */
final class CommentList implements ItemListInterface
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var int
     */
    private $newsID;

    /**
     * @var array
     */
    private $itemIDs = [];

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
        $data  = $this->db->queryPrepared(
            'SELECT tnewskommentar.*, t.title
                FROM tnewskommentar
                JOIN tnewssprache t 
                    ON t.kNews = tnewskommentar.kNews
                WHERE kNewsKommentar IN (' . \implode(',', $this->itemIDs) . ')
                GROUP BY tnewskommentar.kNewsKommentar
                ORDER BY tnewskommentar.dErstellt DESC',
            ['nid' => $this->newsID],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $items = map(group($data, function ($e) {
            return (int)$e->kNewsKommentar;
        }), function ($e, $commentID) {
            $l = new Comment($this->db);
            $l->setID($commentID);
            $l->map($e);
            $l->setNewsTitle(first($e)->title);

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
    public function createItemsByNewsItem(int $newsID): Collection
    {
        $this->newsID = $newsID;
        $data         = $this->db->queryPrepared(
            'SELECT *
                FROM tnewskommentar
                WHERE kNews = :nid
                ORDER BY tnewskommentar.dErstellt DESC',
            ['nid' => $this->newsID],
            ReturnType::ARRAY_OF_OBJECTS
        );
        $items        = map(group($data, function ($e) {
            return (int)$e->kNewsKommentar;
        }), function ($e, $commentID) {
            $l = new Comment($this->db);
            $l->setID($commentID);
            $l->map($e);

            return $l;
        });
        foreach ($items as $item) {
            $this->items->push($item);
        }

        return $this->items;
    }

    /**
     * @param bool $active
     * @return Collection
     */
    public function filter(bool $active): Collection
    {
        return $this->items->filter(function (Comment $e) use ($active) {
            return $e->isActive() === $active;
        });
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param Collection $items
     */
    public function setItems(Collection $items)
    {
        $this->items = $items;
    }

    /**
     * @param Comment $item
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