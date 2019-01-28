<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use dbeS\TableMapper as Mapper;

require_once __DIR__ . '/syncinclude.php';

$return  = 3;
$zipFile = $_FILES['data']['tmp_name'];
if (auth()) {
    $zipFile   = checkFile();
    $return    = 2;
    $unzipPath = PFAD_ROOT . PFAD_DBES . PFAD_SYNC_TMP . basename($zipFile) . '_' . date('dhis') . '/';
    $db        = Shop::Container()->getDB();
    if (($syncFiles = unzipSyncFiles($zipFile, $unzipPath, __FILE__)) === false) {
        Shop::Container()->getLogService()->error('Error: Cannot extract zip file ' . $zipFile . ' to ' . $unzipPath);
        removeTemporaryFiles($zipFile);
    } else {
        $return = 0;
        $db->query('START TRANSACTION', \DB\ReturnType::DEFAULT);
        foreach ($syncFiles as $xmlFile) {
            $d   = file_get_contents($xmlFile);
            $xml = XML_unserialize($d);

            if (isset($xml['tkategorie attr']['nGesamt']) || isset($xml['tkategorie attr']['nAktuell'])) {
                setMetaLimit($xml['tkategorie attr']['nAktuell'], $xml['tkategorie attr']['nGesamt']);
                unset($xml['tkategorie attr']['nGesamt'], $xml['tkategorie attr']['nAktuell']);
            }

            if (strpos($xmlFile, 'katdel.xml') !== false) {
                bearbeiteDeletes($xml);
            } else {
                bearbeiteInsert($xml);
            }
            removeTemporaryFiles($xmlFile);
        }

        \dbeS\LastJob::getInstance()->run(LASTJOBS_KATEGORIEUPDATE, 'Kategorien_xml');
        $db->query('COMMIT', \DB\ReturnType::DEFAULT);
        removeTemporaryFiles(substr($unzipPath, 0, -1), true);
    }
}

echo $return;

/**
 * @param array $xml
 */
function bearbeiteDeletes($xml)
{
    if (!isset($xml['del_kategorien']['kKategorie'])) {
        return;
    }
    if (!is_array($xml['del_kategorien']['kKategorie']) && (int)$xml['del_kategorien']['kKategorie'] > 0) {
        $xml['del_kategorien']['kKategorie'] = [$xml['del_kategorien']['kKategorie']];
    }
    if (!is_array($xml['del_kategorien']['kKategorie'])) {
        return;
    }
    $productIDs = [];
    foreach ($xml['del_kategorien']['kKategorie'] as $kKategorie) {
        $kKategorie = (int)$kKategorie;
        if ($kKategorie > 0) {
            loescheKategorie($kKategorie);
            setCategoryDiscount($kKategorie);

            executeHook(HOOK_KATEGORIE_XML_BEARBEITEDELETES, ['kKategorie' => $kKategorie]);
        }
    }
    $tags = \Functional\map(array_unique(\Functional\flatten($productIDs)), function ($e) {
        return CACHING_GROUP_ARTICLE . '_' . $e;
    });
    Shop::Container()->getCache()->flushTags($tags);
}

/**
 * @param array $xml
 */
function bearbeiteInsert($xml)
{
    $category                 = new stdClass();
    $category->kKategorie     = 0;
    $category->kOberKategorie = 0;
    if (is_array($xml['tkategorie attr'])) {
        $category->kKategorie     = (int)$xml['tkategorie attr']['kKategorie'];
        $category->kOberKategorie = (int)$xml['tkategorie attr']['kOberKategorie'];
    }
    if (!$category->kKategorie) {
        Shop::Container()->getLogService()->error('kKategorie fehlt! XML: ' . print_r($xml, true));

        return;
    }
    if (!is_array($xml['tkategorie'])) {
        return;
    }
    $db = Shop::Container()->getDB();
    // Altes SEO merken => falls sich es bei der aktualisierten Kategorie ändert => Eintrag in tredirect
    $oDataOld      = $db->query(
        'SELECT cSeo, lft, rght, nLevel
            FROM tkategorie
            WHERE kKategorie = ' . $category->kKategorie,
        \DB\ReturnType::SINGLE_OBJECT
    );
    $seoData = getSeoFromDB($category->kKategorie, 'kKategorie', null, 'kSprache');
    loescheKategorie($category->kKategorie);
    $categories = mapArray($xml, 'tkategorie', Mapper::getMapping('mKategorie'));
    if ($categories[0]->kKategorie > 0) {
        if (!$categories[0]->cSeo) {
            $categories[0]->cSeo = \JTL\SeoHelper::getFlatSeoPath($categories[0]->cName);
        }
        $categories[0]->cSeo                  = \JTL\SeoHelper::getSeo($categories[0]->cSeo);
        $categories[0]->cSeo                  = \JTL\SeoHelper::checkSeo($categories[0]->cSeo);
        $categories[0]->dLetzteAktualisierung = 'NOW()';
        $categories[0]->lft                   = $oDataOld->lft ?? 0;
        $categories[0]->rght                  = $oDataOld->rght ?? 0;
        $categories[0]->nLevel                = $oDataOld->nLevel ?? 0;
        DBUpdateInsert('tkategorie', $categories, 'kKategorie');
        if (isset($oDataOld->cSeo)) {
            checkDbeSXmlRedirect($oDataOld->cSeo, $categories[0]->cSeo);
        }
        $db->query(
            "INSERT INTO tseo
                SELECT tkategorie.cSeo, 'kKategorie', tkategorie.kKategorie, tsprache.kSprache
                    FROM tkategorie, tsprache
                    WHERE tkategorie.kKategorie = " . (int)$categories[0]->kKategorie . "
                        AND tsprache.cStandard = 'Y'
                        AND tkategorie.cSeo != ''",
            \DB\ReturnType::DEFAULT
        );

        executeHook(HOOK_KATEGORIE_XML_BEARBEITEINSERT, ['oKategorie' => $categories[0]]);
    }
    $catLanguages = mapArray($xml['tkategorie'], 'tkategoriesprache', Mapper::getMapping('mKategorieSprache'));
    $allLanguages = Sprache::getAllLanguages(1);
    $lCount       = count($catLanguages);
    for ($i = 0; $i < $lCount; ++$i) {
        // Sprachen die nicht im Shop vorhanden sind überspringen
        if (!Sprache::isShopLanguage($catLanguages[$i]->kSprache, $allLanguages)) {
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
        $catLanguages[$i]->cSeo = \JTL\SeoHelper::getSeo($catLanguages[$i]->cSeo);
        $catLanguages[$i]->cSeo = \JTL\SeoHelper::checkSeo($catLanguages[$i]->cSeo);
        DBUpdateInsert('tkategoriesprache', [$catLanguages[$i]], 'kKategorie', 'kSprache');

        $db->delete(
            'tseo',
            ['cKey', 'kKey', 'kSprache'],
            ['kKategorie', (int)$catLanguages[$i]->kKategorie, (int)$catLanguages[$i]->kSprache]
        );
        //insert in tseo
        $oSeo           = new stdClass();
        $oSeo->cSeo     = $catLanguages[$i]->cSeo;
        $oSeo->cKey     = 'kKategorie';
        $oSeo->kKey     = $catLanguages[$i]->kKategorie;
        $oSeo->kSprache = $catLanguages[$i]->kSprache;
        $db->insert('tseo', $oSeo);
        // Insert into tredirect weil sich das SEO vom geändert hat
        if (isset($seoData[$catLanguages[$i]->kSprache])) {
            checkDbeSXmlRedirect(
                $seoData[$catLanguages[$i]->kSprache]->cSeo,
                $catLanguages[$i]->cSeo
            );
        }
    }
    updateXMLinDB(
        $xml['tkategorie'],
        'tkategoriekundengruppe',
        Mapper::getMapping('mKategorieKundengruppe'),
        'kKundengruppe',
        'kKategorie'
    );
    setCategoryDiscount((int)$categories[0]->kKategorie);

    updateXMLinDB(
        $xml['tkategorie'],
        'tkategorieattribut',
        Mapper::getMapping('mKategorieAttribut'),
        'kKategorieAttribut'
    );
    updateXMLinDB(
        $xml['tkategorie'],
        'tkategoriesichtbarkeit',
        Mapper::getMapping('mKategorieSichtbarkeit'),
        'kKundengruppe',
        'kKategorie'
    );
    $attributes = mapArray($xml['tkategorie'], 'tattribut', Mapper::getMapping('mNormalKategorieAttribut'));
    if (count($attributes) > 0) {
        $single = isset($xml['tkategorie']['tattribut attr']) && is_array($xml['tkategorie']['tattribut attr']);
        $i      = 0;
        foreach ($attributes as $attribute) {
            $parentXML = $single ? $xml['tkategorie']['tattribut'] : $xml['tkategorie']['tattribut'][$i++];
            saveKategorieAttribut($parentXML, $attribute);
        }
    }

//        $flushArray = [];
//        $flushArray[] = CACHING_GROUP_CATEGORY . '_' . $Kategorie->kKategorie;
//        if (isset($Kategorie->kOberKategorie) && $Kategorie->kOberKategorie > 0) {
//            $flushArray[] = CACHING_GROUP_CATEGORY . '_' . $Kategorie->kOberKategorie;
//        }
//        Shop::Container()->getCache()->flushTags($flushArray);
    //@todo: the above does not really work on parent categories when adding/deleting child categories
}

/**
 * @param int $kKategorie
 */
function loescheKategorie(int $kKategorie)
{
    $db         = Shop::Container()->getDB();
    $attributes = $db->selectAll(
        'tkategorieattribut',
        'kKategorie',
        $kKategorie,
        'kKategorieAttribut'
    );
    foreach ($attributes as $attribute) {
        deleteKategorieAttribut((int)$attribute->kKategorieAttribut);
    }
    $db->delete('tseo', ['kKey', 'cKey'], [$kKategorie, 'kKategorie']);
    $db->delete('tkategorie', 'kKategorie', $kKategorie);
    $db->delete('tkategoriekundengruppe', 'kKategorie', $kKategorie);
    $db->delete('tkategoriesichtbarkeit', 'kKategorie', $kKategorie);
    $db->delete('tkategoriesprache', 'kKategorie', $kKategorie);
}

/**
 * @param int $kKategorieAttribut
 */
function deleteKategorieAttribut(int $kKategorieAttribut)
{
    Shop::Container()->getDB()->delete('tkategorieattributsprache', 'kAttribut', $kKategorieAttribut);
    Shop::Container()->getDB()->delete('tkategorieattribut', 'kKategorieAttribut', $kKategorieAttribut);
}

/**
 * @param array  $xmlParent
 * @param object $oAttribut
 * @return int
 */
function saveKategorieAttribut($xmlParent, $oAttribut)
{
    // Fix: die Wawi überträgt für die normalen Attribute die ID in kAttribut statt in kKategorieAttribut
    if (!isset($oAttribut->kKategorieAttribut) && isset($oAttribut->kAttribut)) {
        $oAttribut->kKategorieAttribut = (int)$oAttribut->kAttribut;
        unset($oAttribut->kAttribut);
    }
    DBUpdateInsert('tkategorieattribut', [$oAttribut], 'kKategorieAttribut', 'kKategorie');
    $localized = mapArray($xmlParent, 'tattributsprache', Mapper::getMapping('mKategorieAttributSprache'));
    // Die Standardsprache wird nicht separat übertragen und wird deshalb aus den Attributwerten gesetzt
    array_unshift($localized, (object)[
        'kAttribut' => $oAttribut->kKategorieAttribut,
        'kSprache'  => Shop::Container()->getDB()->select('tsprache', 'cShopStandard', 'Y')->kSprache,
        'cName'     => $oAttribut->cName,
        'cWert'     => $oAttribut->cWert,
    ]);
    DBUpdateInsert('tkategorieattributsprache', $localized, 'kAttribut', 'kSprache');

    return $oAttribut->kKategorieAttribut;
}

/**
 * ToDo: Implement different updatestrategies in dependece of total and current category blocks
 * @param $nAktuell
 * @param $nGesamt
 * @return bool
 */
function setMetaLimit($nAktuell, $nGesamt)
{
    return false;
}
