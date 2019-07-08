<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\LastJob;
use JTL\dbeS\Starter;
use JTL\Helpers\Seo;
use JTL\Language\LanguageHelper;
use stdClass;

/**
 * Class Categories
 * @package JTL\dbeS\Sync
 */
final class Categories extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        $this->db->query('START TRANSACTION', ReturnType::DEFAULT);
        foreach ($starter->getXML() as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (isset($xml['tkategorie attr']['nGesamt']) || isset($xml['tkategorie attr']['nAktuell'])) {
                unset($xml['tkategorie attr']['nGesamt'], $xml['tkategorie attr']['nAktuell']);
            }
            if (\strpos($file, 'katdel.xml') !== false) {
                $this->handleDeletes($xml);
            } else {
                $this->handleInserts($xml);
            }
        }
        $lastJob = new LastJob($this->db, $this->logger);
        $lastJob->run(\LASTJOBS_KATEGORIEUPDATE, 'Kategorien_xml');
        $this->db->query('COMMIT', ReturnType::DEFAULT);

        return null;
    }

    /**
     * @param array $xml
     */
    private function handleDeletes(array $xml): void
    {
        if (!isset($xml['del_kategorien']['kKategorie'])) {
            return;
        }
        if (!\is_array($xml['del_kategorien']['kKategorie']) && (int)$xml['del_kategorien']['kKategorie'] > 0) {
            $xml['del_kategorien']['kKategorie'] = [$xml['del_kategorien']['kKategorie']];
        }
        if (!\is_array($xml['del_kategorien']['kKategorie'])) {
            return;
        }
        foreach ($xml['del_kategorien']['kKategorie'] as $categoryID) {
            $categoryID = (int)$categoryID;
            if ($categoryID > 0) {
                $this->deleteCategory($categoryID);
                \executeHook(\HOOK_KATEGORIE_XML_BEARBEITEDELETES, ['kKategorie' => $categoryID]);
            }
        }
    }

    /**
     * @param array $xml
     */
    private function handleInserts(array $xml): void
    {
        $category                 = new stdClass();
        $category->kKategorie     = 0;
        $category->kOberKategorie = 0;
        if (\is_array($xml['tkategorie attr'])) {
            $category->kKategorie     = (int)$xml['tkategorie attr']['kKategorie'];
            $category->kOberKategorie = (int)$xml['tkategorie attr']['kOberKategorie'];
        }
        if (!$category->kKategorie) {
            $this->logger->error('kKategorie fehlt! XML: ' . \print_r($xml, true));

            return;
        }
        if (!\is_array($xml['tkategorie'])) {
            return;
        }
        // Altes SEO merken => falls sich es bei der aktualisierten Kategorie ändert => Eintrag in tredirect
        $oldData    = $this->db->queryPrepared(
            'SELECT cSeo, lft, rght, nLevel
                FROM tkategorie
                WHERE kKategorie = :categoryID',
            [
                'categoryID' => $category->kKategorie,
            ],
            ReturnType::SINGLE_OBJECT
        );
        $seoData    = $this->getSeoFromDB($category->kKategorie, 'kKategorie', null, 'kSprache');
        $categories = $this->mapper->mapArray($xml, 'tkategorie', 'mKategorie');
        if ($categories[0]->kKategorie > 0) {
            if (!$categories[0]->cSeo) {
                $categories[0]->cSeo = Seo::getFlatSeoPath($categories[0]->cName);
            }
            $categories[0]->cSeo                  = Seo::getSeo($categories[0]->cSeo);
            $categories[0]->cSeo                  = Seo::checkSeo($categories[0]->cSeo);
            $categories[0]->dLetzteAktualisierung = 'NOW()';
            $categories[0]->lft                   = $oldData->lft ?? 0;
            $categories[0]->rght                  = $oldData->rght ?? 0;
            $categories[0]->nLevel                = $oldData->nLevel ?? 0;
            $this->insertOnExistUpdate('tkategorie', $categories, ['kKategorie']);
            if (isset($oldData->cSeo)) {
                $this->checkDbeSXmlRedirect($oldData->cSeo, $categories[0]->cSeo);
            }
            $this->db->queryPrepared(
                "INSERT IGNORE INTO tseo
                    SELECT tkategorie.cSeo, 'kKategorie', tkategorie.kKategorie, tsprache.kSprache
                        FROM tkategorie, tsprache
                        WHERE tkategorie.kKategorie = :categoryID
                            AND tsprache.cStandard = 'Y'
                            AND tkategorie.cSeo != ''
                ON DUPLICATE KEY UPDATE
                    cSeo = (SELECT tkategorie.cSeo
                            FROM tkategorie, tsprache
                            WHERE tkategorie.kKategorie = :categoryID
                                    AND tsprache.cStandard = 'Y'
                                    AND tkategorie.cSeo != '')",
                [
                    'categoryID' => (int)$categories[0]->kKategorie
                ],
                ReturnType::DEFAULT
            );

            \executeHook(\HOOK_KATEGORIE_XML_BEARBEITEINSERT, ['oKategorie' => $categories[0]]);
        }
        $catLanguages = $this->mapper->mapArray($xml['tkategorie'], 'tkategoriesprache', 'mKategorieSprache');
        $langIDs      = [];
        $allLanguages = LanguageHelper::getAllLanguages(1);
        $lCount       = \count($catLanguages);
        for ($i = 0; $i < $lCount; ++$i) {
            // Sprachen die nicht im Shop vorhanden sind überspringen
            if (!LanguageHelper::isShopLanguage($catLanguages[$i]->kSprache, $allLanguages)) {
                continue;
            }
            if (!$catLanguages[$i]->cSeo) {
                $catLanguages[$i]->cSeo = $catLanguages[$i]->cName;
            }
            if (!$catLanguages[$i]->cSeo) {
                $catLanguages[$i]->cSeo = $categories[0]->cSeo;
            }
            if (!$catLanguages[$i]->cSeo) {
                $catLanguages[$i]->cSeo = $categories[0]->cName;
            }
            $catLanguages[$i]->cSeo = Seo::getSeo($catLanguages[$i]->cSeo);
            $catLanguages[$i]->cSeo = Seo::checkSeo($catLanguages[$i]->cSeo);
            $this->insertOnExistUpdate('tkategoriesprache', [$catLanguages[$i]], ['kKategorie', 'kSprache']);

            $ins           = new stdClass();
            $ins->cSeo     = $catLanguages[$i]->cSeo;
            $ins->cKey     = 'kKategorie';
            $ins->kKey     = $catLanguages[$i]->kKategorie;
            $ins->kSprache = $catLanguages[$i]->kSprache;
            $this->db->upsert('tseo', $ins);
            if (isset($seoData[$catLanguages[$i]->kSprache])) {
                $this->checkDbeSXmlRedirect(
                    $seoData[$catLanguages[$i]->kSprache]->cSeo,
                    $catLanguages[$i]->cSeo
                );
            }
            $langIDs[] = (int)$catLanguages[$i]->kSprache;
        }
        $this->deleteByKey('tkategoriesprache', ['kKategorie' => $category->kKategorie], 'kSprache', $langIDs);

        $pkValues = $this->insertOnExistsUpdateXMLinDB(
            $xml['tkategorie'],
            'tkategoriekundengruppe',
            'mKategorieKundengruppe',
            ['kKategorie', 'kKundengruppe']
        );
        $this->deleteByKey(
            'tkategoriekundengruppe',
            ['kKategorie' => $category->kKategorie],
            'kKundengruppe',
            $pkValues['kKundengruppe']
        );
        $this->setCategoryDiscount((int)$categories[0]->kKategorie);

        $pkValues = $this->insertOnExistsUpdateXMLinDB(
            $xml['tkategorie'],
            'tkategoriesichtbarkeit',
            'mKategorieSichtbarkeit',
            ['kKundengruppe', 'kKategorie']
        );
        $this->deleteByKey(
            'tkategoriesichtbarkeit',
            ['kKategorie' => $category->kKategorie],
            'kKundengruppe',
            $pkValues['kKundengruppe']
        );

        // Wawi sends category attributes in tkategorieattribut (function attributes)
        // and tattribut (localized attributes) nodes
        $pkValues   = $this->insertOnExistsUpdateXMLinDB(
            $xml['tkategorie'],
            'tkategorieattribut',
            'mKategorieAttribut',
            ['kKategorieAttribut']
        );
        $attributes = $this->mapper->mapArray($xml['tkategorie'], 'tattribut', 'mNormalKategorieAttribut');
        $attribPKs  = $pkValues['kKategorieAttribut'];
        if (\count($attributes) > 0) {
            $single = isset($xml['tkategorie']['tattribut attr']) && \is_array($xml['tkategorie']['tattribut attr']);
            $i      = 0;
            foreach ($attributes as $attribute) {
                $attribPKs[] = $this->saveAttribute(
                    $single ? $xml['tkategorie']['tattribut'] : $xml['tkategorie']['tattribut'][$i++],
                    $attribute
                );
            }
        }
        $this->db->queryPrepared(
            'DELETE tkategorieattribut, tkategorieattributsprache
                FROM tkategorieattribut
                LEFT JOIN tkategorieattributsprache
                    ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                WHERE tkategorieattribut.kKategorie = :categoryID' .(\count($attribPKs) > 0 ? '
                    AND tkategorieattribut.kKategorieAttribut NOT IN (' . \implode(', ', $attribPKs) . ')' : ''),
            ['categoryID' => $category->kKategorie],
            ReturnType::DEFAULT
        );
    }

    /**
     * @param int $id
     */
    private function deleteCategory(int $id): void
    {
        $this->db->queryPrepared(
            'DELETE tkategorieattribut, tkategorieattributsprache
                FROM tkategorieattribut
                LEFT JOIN tkategorieattributsprache
                    ON tkategorieattributsprache.kAttribut = tkategorieattribut.kKategorieAttribut
                WHERE tkategorieattribut.kKategorie = :categoryID',
            ['categoryID' => $id],
            ReturnType::DEFAULT
        );
        $this->db->delete('tseo', ['kKey', 'cKey'], [$id, 'kKategorie']);
        $this->db->delete('tkategorie', 'kKategorie', $id);
        $this->db->delete('tkategoriekundengruppe', 'kKategorie', $id);
        $this->db->delete('tkategoriesichtbarkeit', 'kKategorie', $id);
        $this->db->delete('tkategorieartikel', 'kKategorie', $id);
        $this->db->delete('tkategoriesprache', 'kKategorie', $id);
        $this->db->delete('tartikelkategorierabatt', 'kKategorie', $id);
    }

    /**
     * @param array  $xmlParent
     * @param object $attribute
     * @return int
     */
    private function saveAttribute($xmlParent, $attribute): int
    {
        // Fix: die Wawi überträgt für die normalen Attribute die ID in kAttribut statt in kKategorieAttribut
        if (!isset($attribute->kKategorieAttribut) && isset($attribute->kAttribut)) {
            $attribute->kKategorieAttribut = (int)$attribute->kAttribut;
            unset($attribute->kAttribut);
        }
        $this->insertOnExistUpdate('tkategorieattribut', [$attribute], ['kKategorieAttribut', 'kKategorie']);
        $localized = $this->mapper->mapArray($xmlParent, 'tattributsprache', 'mKategorieAttributSprache');
        // Die Standardsprache wird nicht separat übertragen und wird deshalb aus den Attributwerten gesetzt
        \array_unshift($localized, (object)[
            'kAttribut' => $attribute->kKategorieAttribut,
            'kSprache'  => $this->db->select('tsprache', 'cShopStandard', 'Y')->kSprache,
            'cName'     => $attribute->cName,
            'cWert'     => $attribute->cWert,
        ]);
        $this->upsert('tkategorieattributsprache', $localized, 'kAttribut', 'kSprache');
        $pkValues = $this->insertOnExistUpdate('tkategorieattributsprache', $localized, ['kAttribut', 'kSprache']);
        $this->deleteByKey(
            'tkategorieattributsprache',
            ['kAttribut' => $attribute->kKategorieAttribut],
            'kSprache',
            $pkValues['kSprache']
        );

        return (int)$attribute->kKategorieAttribut;
    }

    /**
     * @param int $categoryID
     */
    private function setCategoryDiscount(int $categoryID): void
    {
        $this->db->delete('tartikelkategorierabatt', 'kKategorie', $categoryID);
        $this->db->queryPrepared(
            'INSERT INTO tartikelkategorierabatt (
                SELECT tkategorieartikel.kArtikel, tkategoriekundengruppe.kKundengruppe, tkategorieartikel.kKategorie,
                       MAX(tkategoriekundengruppe.fRabatt) fRabatt
                FROM tkategoriekundengruppe
                INNER JOIN tkategorieartikel
                    ON tkategorieartikel.kKategorie = tkategoriekundengruppe.kKategorie
                LEFT JOIN tkategoriesichtbarkeit
                    ON tkategoriesichtbarkeit.kKategorie = tkategoriekundengruppe.kKategorie
                    AND tkategoriesichtbarkeit.kKundengruppe = tkategoriekundengruppe.kKundengruppe
                WHERE tkategoriekundengruppe.kKategorie = :categoryID
                    AND tkategoriesichtbarkeit.kKategorie IS NULL
                GROUP BY tkategorieartikel.kArtikel, tkategoriekundengruppe.kKundengruppe, tkategorieartikel.kKategorie
                HAVING MAX(tkategoriekundengruppe.fRabatt) > 0)',
            ['categoryID' => $categoryID],
            ReturnType::DEFAULT
        );
        $this->cache->flushTags([\CACHING_GROUP_CATEGORY . '_' . $categoryID]);
    }
}
