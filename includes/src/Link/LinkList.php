<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\Link;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use function Functional\group;
use function Functional\map;

/**
 * Class LinkList
 * @package JTL\Link
 */
final class LinkList implements LinkListInterface
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var int[]
     */
    private $linkIDs;

    /**
     * @var Collection
     */
    private $links;

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
        $this->linkIDs = \array_map('\intval', $linkIDs);
        if (\count($this->linkIDs) === 0) {
            return $this->links;
        }
        $realIDs  = $this->db->query(
            'SELECT `kLink`, `reference`, `kVaterLink`
                FROM tlink 
                WHERE kLink IN (' . \implode(',', $this->linkIDs) . ')',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $loadData = [];
        $realData = [];
        foreach ($realIDs as $realID) {
            $ref             = (int)$realID->reference;
            $link            = (int)$realID->kLink;
            $real            = $ref > 0 ? $ref : $link;
            $realData[$link] = $real;
            $loadData[$real] = (object)['linkID' => $link, 'parentID' => (int)$realID->kVaterLink];
        }
        $linkLanguages = $this->db->query(
            "SELECT tlink.*, tlinksprache.cISOSprache,
                tlink.cName AS displayName,
                tlinksprache.cName AS localizedName, 
                tlinksprache.cTitle AS localizedTitle, 
                tsprache.kSprache, 
                tlinksprache.cContent AS content,
                tlinksprache.cMetaDescription AS metaDescription,
                tlinksprache.cMetaKeywords AS metaKeywords,
                tlinksprache.cMetaTitle AS metaTitle,
                tseo.kSprache AS languageID,
                tseo.cSeo AS localizedUrl,
                tspezialseite.cDateiname,
                tplugin.nStatus AS pluginState,
                GROUP_CONCAT(tlinkgroupassociations.linkGroupID) AS linkGroups
            FROM tlink
                JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                JOIN tsprache
                    ON tsprache.cISO = tlinksprache.cISOSprache
                JOIN tseo
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = tlinksprache.kLink
                    AND tseo.kSprache = tsprache.kSprache
                LEFT JOIN tlinkgroupassociations
                    ON tlinkgroupassociations.linkID = tlinksprache.kLink
                LEFT JOIN tspezialseite
                    ON tspezialseite.nLinkart = tlink.nLinkart
                LEFT JOIN tplugin
                    ON tplugin.kPlugin = tlink.kPlugin
                WHERE tlink.kLink IN (" . \implode(',', $realData) . ')
                GROUP BY tlink.kLink, tseo.kSprache
                ORDER BY tlink.nSort, tlink.cName',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $links         = map(group($linkLanguages, function ($e) {
            return (int)$e->kLink;
        }), function ($e, $linkID) use ($loadData) {
            $referenced = $loadData[$linkID]->linkID;
            $l          = new Link($this->db);
            $l->setID($loadData[$linkID]->linkID);
            $l->map($e);
            if ($referenced !== $linkID) {
                $l->setReference($linkID);
                $l->setParent($loadData[$linkID]->parentID);
            }

            return $l;
        });
        foreach ($links as $link) {
            $this->links->push($link);
        }

        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function setLinks(Collection $links): void
    {
        $this->links = $links;
    }

    /**
     * @inheritdoc
     */
    public function addLink(LinkInterface $link): void
    {
        $this->links->push($link);
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
