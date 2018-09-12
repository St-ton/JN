<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace Sitemap\Factories;

use function Functional\first;

/**
 * Class Product
 * @package Sitemap\Generators
 */
final class Product extends AbstractFactory
{
    /**
     * @inheritdoc
     */
    public function getCollection(array $languages, array $customerGroups): \Generator
    {
        $defaultCustomerGroupID                                      = first($customerGroups);
        $defaultLang                                                 = \Sprache::getDefaultLanguage(true);
        $defaultLangID                                               = (int)$defaultLang->kSprache;
        $_SESSION['kSprache']                                        = $defaultLangID;
        $_SESSION['cISOSprache']                                     = $defaultLang->cISO;
        $andWhere                                                    = '';
        if ($this->config['sitemap']['sitemap_varkombi_children_export'] !== 'Y') {
            $andWhere .= ' AND tartikelzwei.kVaterArtikel = 0';
        }
        if ((int)$this->config['global']['artikel_artikelanzeigefilter'] === \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER) {
            $andWhere .= " AND (tartikelzwei.cLagerBeachten = 'N' OR tartikelzwei.fLagerbestand > 0)";
        } elseif ((int)$this->config['global']['artikel_artikelanzeigefilter'] === \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL) {
            $andWhere .= " AND (tartikelzwei.cLagerBeachten = 'N' 
                            OR tartikelzwei.cLagerKleinerNull = 'Y' 
                            OR tartikelzwei.fLagerbestand > 0)";
        }
        foreach ($languages as $SpracheTMP) {
            if ($SpracheTMP->kSprache === $defaultLangID) {
                $res = $this->db->queryPrepared(
                    "SELECT tartikelzwei.kArtikel, tartikelzwei.dLetzteAktualisierung AS dlm, tseo.cSeo
                        FROM tartikelzwei
                            LEFT JOIN tartikelsichtbarkeit 
                                ON tartikelzwei.kArtikel = tartikelsichtbarkeit.kArtikel
                                AND tartikelsichtbarkeit.kKundengruppe = :kGrpID 
                            LEFT JOIN tseo 
                                ON tseo.cKey = 'kArtikel'
                                AND tseo.kKey = tartikelzwei.kArtikel
                                AND tseo.kSprache = :langID
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL" . $andWhere,
                    [
                        'kGrpID' => $defaultCustomerGroupID,
                        'langID' => $defaultLangID
                    ],
                    \DB\ReturnType::QUERYSINGLE
                );
            } else {
                $res = $this->db->queryPrepared(
                    "SELECT tartikelzwei.kArtikel, tartikelzwei.dLetzteAktualisierung AS dlm, tseo.cSeo
                        FROM tartikelsprache
                            JOIN tartikelzwei
                            ON tartikelzwei.kArtikel = tartikelsprache.kArtikel
                        JOIN tseo 
                            ON tseo.cKey = 'kArtikel'
                            AND tseo.kKey = tartikelzwei.kArtikel
                            AND tseo.kSprache = :langID
                        LEFT JOIN tartikelsichtbarkeit 
                            ON tartikelzwei.kArtikel = tartikelsichtbarkeit.kArtikel
                            AND tartikelsichtbarkeit.kKundengruppe = :kGrpID
                        WHERE tartikelsichtbarkeit.kArtikel IS NULL
                            AND tartikelzwei.kVaterArtikel = 0 
                            AND tartikelsprache.kSprache = :langID
                        ORDER BY tartikelzwei.kArtikel",
                    [
                        'kGrpID' => $defaultCustomerGroupID,
                        'langID' => $SpracheTMP->kSprache
                    ],
                    \DB\ReturnType::QUERYSINGLE
                );
            }

            while (($product = $res->fetch(\PDO::FETCH_OBJ)) !== false) {
                $item = new \Sitemap\Items\Product($this->config, $this->baseURL, $this->baseImageURL);
                $item->generateData($product);
                yield $item;
            }
        }
    }
}
