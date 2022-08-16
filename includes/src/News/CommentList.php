<?php declare(strict_types=1);

namespace JTL\News;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use function Functional\first;
use function Functional\group;
use function Functional\map;

/**
 * Class CommentList
 * @package JTL\News
 */
final class CommentList implements ItemListInterface
{
    /**
     * @var DbInterface|null
     */
    private ?DbInterface $db;

    /**
     * @var int
     */
    private int $newsID;

    /**
     * @var Collection
     */
    private Collection $items;

    /**
     * CommentList constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db    = $db;
        $this->items = new Collection();
    }

    /**
     * @inheritDoc
     */
    public function createItems(array $itemIDs, bool $activeOnly = true): Collection
    {
        if (\count($itemIDs) === 0) {
            return $this->items;
        }
        $itemIDs = \array_map('\intval', $itemIDs);
        $data    = $this->db->getObjects(
            'SELECT tnewskommentar.*, t.title
                FROM tnewskommentar
                JOIN tnewssprache t 
                    ON t.kNews = tnewskommentar.kNews
                WHERE kNewsKommentar IN (' . \implode(',', $itemIDs) . ')'
                . ($activeOnly ? ' AND nAktiv = 1 ' : '') . '
                GROUP BY tnewskommentar.kNewsKommentar
                ORDER BY tnewskommentar.dErstellt DESC'
        );
        $items   = map(group($data, static function ($e): int {
            return (int)$e->kNewsKommentar;
        }), function ($e, $commentID): Comment {
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
     * @param int $newsID
     * @return Collection
     */
    public function createItemsByNewsItem(int $newsID): Collection
    {
        $this->newsID = $newsID;
        $data         = $this->db->getObjects(
            'SELECT *
                FROM tnewskommentar
                WHERE kNews = :nid
                    AND nAktiv = 1
                    ORDER BY tnewskommentar.dErstellt DESC',
            ['nid' => $this->newsID]
        );
        $items        = map(group($data, static function ($e): int {
            return (int)$e->kNewsKommentar;
        }), function ($e, $commentID): Comment {
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
        return $this->items->filter(static function (Comment $e) use ($active): bool {
            return $e->isActive() === $active;
        });
    }

    /**
     * @inheritDoc
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param string $whatcount
     * @return int
     */
    public function getCommentsCount(string $whatcount = 'parent'): int
    {
        $parent = 0;
        $child  = 0;
        foreach ($this->items as $comment) {
            if ($comment->getParentCommentID() === 0) {
                $parent++;
            } else {
                $child++;
            }
        }

        return $whatcount === 'parent' ? $parent : $child;
    }

    /**
     * @return Collection
     */
    public function getThreadedItems(): Collection
    {
        /** @var Comment $comment */
        foreach ($this->items as $comment) {
            /** @var Comment $child */
            foreach ($this->items as $child) {
                if ($comment->getID() === $child->getParentCommentID()) {
                    $comment->setChildComment($child);
                }
            }
        }
        foreach ($this->items as $key => $comment) {
            if ($comment->getParentCommentID() > 0) {
                unset($this->items[$key]);
            }
        }

        return $this->items;
    }

    /**
     * @inheritDoc
     */
    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }

    /**
     * @inheritDoc
     */
    public function addItem($item): void
    {
        $this->items->push($item);
    }

    /**
     * @return DbInterface|null
     */
    public function getDB(): ?DbInterface
    {
        return $this->db;
    }

    /**
     * @param DbInterface $db
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @return int
     */
    public function getNewsID(): int
    {
        return $this->newsID;
    }

    /**
     * @param int $newsID
     */
    public function setNewsID(int $newsID): void
    {
        $this->newsID = $newsID;
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
