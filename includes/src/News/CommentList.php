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
 * Class CommentList
 * @package News
 */
final class CommentList
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
     * @param int $newsID
     * @return Collection
     */
    public function getComments(int $newsID): Collection
    {
        $this->newsID = $newsID;
        $data         = $this->db->queryPrepared(
            'SELECT *
                FROM tnewskommentar
                WHERE kNews = :nid
                    AND nAktiv = 1
                ORDER BY tnewskommentar.dErstellt DESC',
            ['nid' => $newsID],
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
    public function addItem(Comment $item)
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