<?php declare(strict_types=1);
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use Link\Link;
use Link\LinkList;
use function Functional\first;
use function Functional\map;

/**
 * Class Page
 * @package Sitemap\Generators
 */
final class Page extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): \Generator
    {
        $customerGroup = first($customerGroups);
        $languageCodes = map($languages, function ($e) {
            return "'" . $e->cISO . "'";
        });
        $res           = $this->db->queryPrepared(
            "SELECT DISTINCT tlink.kLink AS id
                FROM tlink
                JOIN tlinkgroupassociations
                    ON tlinkgroupassociations.linkID = tlink.kLink
                JOIN tseo
                    ON tseo.cKey = 'kLink'
                    AND tseo.kKey = tlink.kLink
                JOIN tlinkgruppe 
                    ON tlinkgroupassociations.linkGroupID = tlinkgruppe.kLinkgruppe
                JOIN tlinksprache
                    ON tlinksprache.kLink = tlink.kLink
                WHERE tlink.cSichtbarNachLogin = 'N'
                    AND tlink.cNoFollow = 'N'
                    AND tlink.bIsActive = 1
                    AND tlink.nLinkart != " . \LINKTYP_EXTERNE_URL . "
                    AND tlinkgruppe.cName != 'hidden'
                    AND tlinkgruppe.cTemplatename != 'hidden'
                    AND tlinksprache.cISOSprache IN (" . \implode(',', $languageCodes) . ")
                    AND (tlink.cKundengruppen IS NULL
                        OR tlink.cKundengruppen = 'NULL'
                        OR FIND_IN_SET(:cGrpID, REPLACE(tlink.cKundengruppen, ';', ',')) > 0)
                ORDER BY tlinksprache.kLink",
            ['cGrpID' => $customerGroup],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $linkIDs       = map($res, function ($e) {
            return $e->id;
        });
        $linkList      = new LinkList($this->db);
        $linkList->createLinks($linkIDs);
        foreach ($linkList->getLinks()->all() as $link) {
            /** @var Link $link */
            $linkType = $link->getLinkType();
            foreach ($link->getURLs() as $i => $url) {
                $data           = new \stdClass();
                $data->kLink    = $link->getID();
                $data->cSEO     = $url;
                $data->nLinkart = $linkType;
                $data->langID   = $link->getLanguageID($i);
                $data->langCode = $link->getLanguageCode($i);
                $item           = new \Sitemap\Items\Page($this->config, $this->baseURL, $this->baseImageURL);
                $item->generateData($data, $languages);
                yield $item;
            }
        }
    }
}
