<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link;


use DB\DbInterface;
use DB\ReturnType;
use function Functional\group;
use function Functional\map;
use Tightenco\Collect\Support\Collection;

/**
 * Class LinkList
 * @package Link
 */
class LinkList implements LinkListInterface
{
    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var int[]
     */
    protected $linkIDs;

    /**
     * @var Collection
     */
    protected $links;

    /**
     * LinkList constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db    = $db;
        $this->links = new Collection();
    }

    /**
     * @inheritdoc
     */
    public function createLinks(array $linkIDs): Collection
    {
        $this->linkIDs = array_map('intval', $linkIDs);
        $linkLanguages = $this->db->query(
            "SELECT tlink.*, tlinksprache.cISOSprache, 
                tlinksprache.cName AS localizedName, 
                tlinksprache.cTitle AS localizedTitle, 
                tlinksprache.kSprache, 
                tlinksprache.cContent AS content,
                tlinksprache.cMetaDescription AS metaDescription,
                tlinksprache.cMetaKeywords AS metaKeywords,
                tlinksprache.cMetaTitle AS metaTitle,
                tseo.kSprache AS languageID,
                tseo.cSeo AS localizedUrl,
                tplugin.nStatus AS pluginState
            FROM tlink
                JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                JOIN tsprache
                    ON tsprache.cISO = tlinksprache.cISOSprache
                JOIN tseo
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = tlinksprache.kLink
                    AND tseo.kSprache = tsprache.kSprache
                LEFT JOIN tplugin
                    ON tplugin.kPlugin = tlink.kPlugin
                WHERE tlinksprache.kLink IN (" . implode(',', $this->linkIDs) . ")
                ORDER BY tlink.nSort, tlink.cName",
            ReturnType::ARRAY_OF_OBJECTS
        );
        $links         = map(group($linkLanguages, function ($e) {
            return (int)$e->kLink;
        }), function ($e, $linkID) {
            $l = new Link($this->db);
            $l->setID($linkID);
            $l->map($e);

            return $l;
        });
        foreach ($links as $link) {
            $this->links->push($link);
        }

        return $this->links;
    }

    /**
     * @return Collection
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function setLinks(Collection $links)
    {
        $this->links = $links;
    }

    public function addLink(LinkInterface $link)
    {
        $this->links->push($link);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $res       = get_object_vars($this);
        $res['db'] = '*truncated*';

        return $res;
    }
}