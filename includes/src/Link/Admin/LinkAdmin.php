<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Link\Admin;


use Cache\JTLCacheInterface;
use DB\DbInterface;
use DB\ReturnType;
use Link\Link;
use Link\LinkGroupCollection;
use Link\LinkGroupInterface;
use Link\LinkGroupList;
use Link\LinkInterface;

/**
 * Class LinkAdmin
 * @package Link\Admin
 */
final class LinkAdmin
{

    const ERROR_LINK_ALREADY_EXISTS = 1;

    const ERROR_LINK_NOT_FOUND = 2;

    const ERROR_LINK_GROUP_NOT_FOUND = 3;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * LinkAdmin constructor.
     * @param DbInterface $db #
     */
    public function __construct(DbInterface $db, JTLCacheInterface $cache)
    {
        $this->db    = $db;
        $this->cache = $cache;
    }

    /**
     * @return LinkGroupCollection
     */
    public function getLinkGroups(): LinkGroupCollection
    {
        $lgl = new LinkGroupList($this->db, $this->cache);
        $lgl->loadAll();
        $linkGroups = $lgl->getLinkGroups()->filter(function (LinkGroupInterface $e) {
            return $e->isSpecial() === false || $e->getTemplate() === 'unassigned';
        });
        foreach ($linkGroups as $linkGroup) {
            /** @var LinkGroupInterface $linkGroup */
            $filtered = build_navigation_subs_admin($linkGroup);
            $linkGroup->setLinks($filtered);
        }

        return $linkGroups;
    }

    /**
     * @param int $id
     * @param     $post
     * @return \stdClass
     */
    public function createOrUpdateLinkGroup(int $id = 0, $post): \stdClass
    {
        $linkGroup                = new \stdClass();
        $linkGroup->kLinkgruppe   = (int)$post['kLinkgruppe'];
        $linkGroup->cName         = htmlspecialchars($post['cName'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
        $linkGroup->cTemplatename = htmlspecialchars($post['cTemplatename'], ENT_COMPAT | ENT_HTML401, JTL_CHARSET);

        if ($id === 0) {
            $kLinkgruppe = $this->db->insert('tlinkgruppe', $linkGroup);
        } else {
            $kLinkgruppe = (int)$post['kLinkgruppe'];
            $this->db->update('tlinkgruppe', 'kLinkgruppe', $kLinkgruppe, $linkGroup);
        }
        $sprachen                       = gibAlleSprachen();
        $linkgruppeSprache              = new \stdClass();
        $linkgruppeSprache->kLinkgruppe = $kLinkgruppe;
        foreach ($sprachen as $sprache) {
            $linkgruppeSprache->cISOSprache = $sprache->cISO;
            $linkgruppeSprache->cName       = $linkGroup->cName;
            if ($post['cName_' . $sprache->cISO]) {
                $linkgruppeSprache->cName = htmlspecialchars($post['cName_' . $sprache->cISO],
                    ENT_COMPAT | ENT_HTML401, JTL_CHARSET);
            }

            $this->db->delete(
                'tlinkgruppesprache',
                ['kLinkgruppe', 'cISOSprache'],
                [$kLinkgruppe, $sprache->cISO]
            );
            $this->db->insert('tlinkgruppesprache', $linkgruppeSprache);
        }

        return $linkGroup;
    }

    /**
     * @return array
     */
    public function getLinkGroupCountForLinkIDs(): array
    {
        $assocCount             = $this->db->query(
            'SELECT tlink.kLink, COUNT(*) AS cnt 
                FROM tlink 
                JOIN tlinkgroupassociations
                    ON tlinkgroupassociations.linkID = tlink.kLink
                GROUP BY tlink.kLink
                HAVING COUNT(*) > 1',
            ReturnType::ARRAY_OF_OBJECTS
        );
        $linkGroupCountByLinkID = [];
        foreach ($assocCount as $item) {
            $linkGroupCountByLinkID[(int)$item->kLink] = (int)$item->cnt;
        }

        return $linkGroupCountByLinkID;
    }

    /**
     * @param int $linkID
     * @param int $linkGroupID
     * @return int
     */
    public function removeLinkFromLinkGroup(int $linkID, int $linkGroupID): int
    {
        $link = (new Link($this->db))->load($linkID);
        foreach ($link->getChildLinks() as $childLink) {
            $this->removeLinkFromLinkGroup($childLink->getID(), $linkGroupID);
        }

        return $this->db->delete(
            'tlinkgroupassociations',
            ['linkGroupID', 'linkID'],
            [$linkGroupID, $linkID]
        );
    }

    /**
     * @param int $linkID
     * @param int $parentLinkID
     * @return bool|\stdClass
     */
    public function updateParentID(int $linkID, int $parentLinkID)
    {
        $oLink      = $this->db->select('tlink', 'kLink', $linkID);
        $oVaterLink = $this->db->select('tlink', 'kLink', $parentLinkID);

        if (isset($oLink->kLink)
            && $oLink->kLink > 0
            && ((isset($oVaterLink->kLink) && $oVaterLink->kLink > 0) || $parentLinkID === 0)
        ) {
            $upd             = new \stdClass();
            $upd->kVaterLink = $parentLinkID;
            $this->db->update('tlink', 'kLink', $linkID, $upd);

            return $oLink;
        }

        return false;
    }

    /**
     * @param int $linkID
     * @return int
     */
    public function deleteLink($linkID): int
    {
        return $this->db->executeQueryPrepared(
            "DELETE tlink, tlinksprache, tseo, tlinkgroupassociations
                FROM tlink
                LEFT JOIN tlinkgroupassociations
                    ON tlinkgroupassociations.linkID = tlink.kLink
                LEFT JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                LEFT JOIN tseo
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = :lid
                WHERE tlink.kLink = :lid",
            ['lid' => $linkID],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param int  $linkGroupID
     * @param bool $names
     * @return array
     */
    public function getPreDeletionLinks(int $linkGroupID, bool $names = true): array
    {
        $links = $this->db->queryPrepared(
            'SELECT tlink.cName
                FROM tlink
                JOIN tlinkgroupassociations A
                    ON tlink.kLink = A.linkID
                JOIN tlinkgroupassociations B
                    ON A.linkID = B.linkID
                WHERE A.linkGroupID = :lgid
                GROUP BY A.linkID
                HAVING COUNT(A.linkID) > 1',
            ['lgid' => $linkGroupID],
            ReturnType::ARRAY_OF_OBJECTS
        );

        return $names === true
            ? \Functional\map($links, function ($l) {
                return $l->cName;
            })
            : $links;

    }

    /**
     * @param int $id
     * @return array
     */
    public function getMissingLinkTranslations(int $id): array
    {
        return $this->db->queryPrepared(
            'SELECT tlink.*,tsprache.*
                FROM tlink
                JOIN tsprache
                LEFT JOIN tlinksprache
                    ON tlink.kLink = tlinksprache.kLink
                    AND tlinksprache.cISOSprache = tsprache.cISO
                LEFT JOIN tsprache t2
                    ON t2.cISO = tlinksprache.cISOSprache
                    AND t2.cISO = tsprache.cISO
                WHERE t2.cISO IS NULL
                    AND tlink.kLink = :lid',
            ['lid' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param int $id
     * @return array
     */
    public function getMissingLinkGroupTranslations(int $id): array
    {
        return $this->db->queryPrepared(
            'SELECT tlinkgruppe.*, tsprache.* 
                FROM tlinkgruppe
                JOIN tsprache
                LEFT JOIN tlinkgruppesprache
                    ON tlinkgruppe.kLinkgruppe = tlinkgruppesprache.kLinkgruppe
                    AND tlinkgruppesprache.cISOSprache = tsprache.cISO
                LEFT JOIN tsprache t2
                    ON t2.cISO = tlinkgruppesprache.cISOSprache
                    AND t2.cISO = tsprache.cISO
                WHERE t2.cISO IS NULL
                    AND tlinkgruppe.kLinkgruppe = :lgid',
            ['lgid' => $id],
            ReturnType::ARRAY_OF_OBJECTS
        );
    }

    /**
     * @param int $linkID
     * @param int $targetLinkGroupID
     * @return int|Link
     */
    public function copyLinkToLinkGroup(int $linkID, int $targetLinkGroupID)
    {
        $link = new Link($this->db);
        $link->load($linkID);
        if ($link->getID() === 0) {
            return self::ERROR_LINK_NOT_FOUND;
        }
        $oLinkgruppe = $this->db->select('tlinkgruppe', 'kLinkgruppe', $targetLinkGroupID);
        if (!isset($oLinkgruppe->kLinkgruppe) || $oLinkgruppe->kLinkgruppe <= 0) {
            return self::ERROR_LINK_GROUP_NOT_FOUND;
        }
        $exists = $this->db->select(
            'tlinkgroupassociations',
            ['linkID', 'linkGroupID'],
            [$linkID, $targetLinkGroupID]
        );
        if (!empty($exists)) {
            return self::ERROR_LINK_ALREADY_EXISTS;
        }
        $ins              = new \stdClass();
        $ins->linkID      = $link->getID();
        $ins->linkGroupID = $targetLinkGroupID;
        $this->db->insert('tlinkgroupassociations', $ins);
        $this->copyChildLinksToLinkGroup($link, $targetLinkGroupID);

        return $link;
    }

    /**
     * @param int $linkID
     * @param int $oldLinkGroupID
     * @param int $newLinkGroupID
     * @return int|Link
     */
    public function updateLinkGroup(int $linkID, int $oldLinkGroupID, int $newLinkGroupID)
    {
        $link = new Link($this->db);
        $link->load($linkID);
        if ($link->getID() === 0) {
            return self::ERROR_LINK_NOT_FOUND;
        }
        $linkgruppe = $this->db->select('tlinkgruppe', 'kLinkgruppe', $newLinkGroupID);
        if (!isset($linkgruppe->kLinkgruppe) || $linkgruppe->kLinkgruppe <= 0) {
            return self::ERROR_LINK_GROUP_NOT_FOUND;
        }
        $exists = $this->db->select(
            'tlinkgroupassociations',
            ['linkGroupID', 'linkID'],
            [$newLinkGroupID, $link->getID()]
        );
        if (!empty($exists)) {
            return self::ERROR_LINK_ALREADY_EXISTS;
        }
        $upd              = new \stdClass();
        $upd->linkGroupID = $newLinkGroupID;
        $rows             = $this->db->update(
            'tlinkgroupassociations',
            ['linkGroupID', 'linkID'],
            [$oldLinkGroupID, $link->getID()],
            $upd
        );
        if ($rows === 0) {
            // previously unassigned link
            $upd              = new \stdClass();
            $upd->linkGroupID = $newLinkGroupID;
            $upd->linkID      = $link->getID();
            $this->db->insert('tlinkgroupassociations', $upd);
        }
        unset($upd->linkID);
        $this->updateChildLinkGroups($link, $oldLinkGroupID, $newLinkGroupID);

        return $link;
    }

    /**
     * @param int $linkGroupID
     * @return int
     */
    public function deleteLinkGroup(int $linkGroupID): int
    {
        $linkIDs = $this->db->selectAll('tlinkgroupassociations', 'linkGroupID', $linkGroupID);
        foreach ($linkIDs as $linkID) {
            $this->deleteLink((int)$linkID->linkID);
        }
        $res = $this->db->delete('tlinkgruppe', 'kLinkgruppe', $linkGroupID);
        $this->db->delete('tlinkgruppesprache', 'kLinkgruppe', $linkGroupID);

        return $res;
    }

    /**
     * @param LinkInterface $link
     * @param int           $old
     * @param int           $new
     */
    private function updateChildLinkGroups(LinkInterface $link, int $old, int $new)
    {
        $upd              = new \stdClass();
        $upd->linkGroupID = $new;
        foreach ($link->getChildLinks() as $childLink) {
            if ($old < 0) {
                // previously unassigned
                $ins              = new \stdClass();
                $ins->linkGroupID = $new;
                $ins->linkID      = $childLink->getID();
                $this->db->insert(
                    'tlinkgroupassociations',
                    $ins
                );
            } else {
                $this->db->update(
                    'tlinkgroupassociations',
                    ['linkGroupID', 'linkID'],
                    [$old, $childLink->getID()],
                    $upd
                );
            }
            $this->updateChildLinkGroups($childLink, $old, $new);
        }
    }

    /**
     * @param LinkInterface $link
     * @param int           $linkGroupID
     */
    public function copyChildLinksToLinkGroup(LinkInterface $link, int $linkGroupID)
    {
        $link->buildChildLinks();
        $ins              = new \stdClass();
        $ins->linkGroupID = $linkGroupID;
        foreach ($link->getChildLinks() as $childLink) {
            $ins->linkID = $childLink->getID();
            $this->db->insert(
                'tlinkgroupassociations',
                $ins
            );
            $this->copyChildLinksToLinkGroup($childLink, $linkGroupID);
        }
    }
}
