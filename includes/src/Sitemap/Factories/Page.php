<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use Link\Link;
use Link\LinkList;
use Tightenco\Collect\Support\Collection;
use function Functional\first;
use function Functional\map;

/**
 * Class Page
 * @package Sitemap\Generators
 */
class Page extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): Collection
    {
        $collection = new Collection();
        if ($this->config['sitemap']['sitemap_seiten_anzeigen'] !== 'Y') {
            return $collection;
        }
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
                #GROUP BY tlinksprache.kLink, tlinksprache.cISOSprache
                ORDER BY tlinksprache.kLink",
            ['cGrpID' => $customerGroup],
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        $linkIDs       = map($res, function ($e) {
            return $e->id;
        });
        $linkList      = new LinkList($this->db);
        $linkList->createLinks($linkIDs);
        $linkList->getLinks()->each(function (Link $e) use ($collection) {
            $linkType = $e->getLinkType();
            foreach ($e->getURLs() as $url) {
                $data           = new \stdClass();
                $data->cSEO     = $url;
                $data->nLinkart = $linkType;
                $item           = new \Sitemap\Items\Page($this->config, $this->baseURL, $this->baseImageURL);
                $item->generateData($data);
                $collection->push($item);
            }
        });

        return $collection;
    }
}
