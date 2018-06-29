<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_PRODUCTTAGS_VIEW', true, true);

require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'tagging_inc.php';
/** @global JTLSmarty $smarty */
setzeSprache();

$cHinweis          = '';
$cFehler           = '';
$step              = 'uebersicht';
$settingsIDs       = [427, 428, 431, 433, 434, 435, 430];
// Tabs
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
if (isset($_POST['tagging']) && (int)$_POST['tagging'] === 1 && FormHelper::validateToken()) {
    //Formular wurde abgeschickt
    if (!isset($_POST['delete'])) {
        if (is_array($_POST['kTagAll']) && count($_POST['kTagAll']) > 0) {
            $cSQLDel = ' IN (';
            foreach ($_POST['kTagAll'] as $i => $kTagAll) {
                $upd         = new stdClass();
                $upd->nAktiv = 0;
                Shop::Container()->getDB()->update('ttag', 'kTag', (int)$kTagAll, $upd);
                // Loeschequery vorbereiten
                if ($i > 0) {
                    $cSQLDel .= ', ' . (int)$kTagAll;
                } else {
                    $cSQLDel .= (int)$kTagAll;
                }
            }
            $cSQLDel .= ')';
            // Deaktivierten Tag aus tseo loeschen
            Shop::Container()->getDB()->query(
                "DELETE FROM tseo
                    WHERE cKey = 'kTag'
                        AND kKey" . $cSQLDel,
                \DB\ReturnType::AFFECTED_ROWS
            );
            // Deaktivierten Tag in ttag updaten
            Shop::Container()->getDB()->query(
                "UPDATE ttag
                    SET cSeo = ''
                    WHERE kTag" . $cSQLDel,
                \DB\ReturnType::AFFECTED_ROWS
            );
            // nAktiv Reihe updaten
            if (is_array($_POST['nAktiv'])) {
                foreach ($_POST['nAktiv'] as $i => $nAktiv) {
                    $oTag = Shop::Container()->getDB()->select('ttag', 'kTag', (int)$nAktiv);
                    Shop::Container()->getDB()->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kTag', (int)$nAktiv, (int)$_SESSION['kSprache']]
                    );
                    // Aktivierten Tag in tseo eintragen
                    $oSeo           = new stdClass();
                    $oSeo->cSeo     = isset($oTag->cName)
                        ? checkSeo(getSeo($oTag->cName))
                        : '';
                    $oSeo->cKey     = 'kTag';
                    $oSeo->kKey     = $nAktiv;
                    $oSeo->kSprache = $_SESSION['kSprache'];
                    Shop::Container()->getDB()->insert('tseo', $oSeo);
                    // Aktivierte Suchanfragen in tsuchanfrage updaten
                    $upd         = new stdClass();
                    $upd->nAktiv = 1;
                    $upd->cSeo   = $oSeo->cSeo;
                    Shop::Container()->getDB()->update('ttag', 'kTag', $nAktiv, $upd);
                }
            }
            flushAffectedArticleCache($_POST['kTagAll']);
        }
        // Eintragen in die Mapping Tabelle
        $Tags = Shop::Container()->getDB()->query(
            "SELECT ttag.kTag, ttag.cName, ttag.nAktiv, sum(ttagartikel.nAnzahlTagging) AS Anzahl 
                FROM ttag
                JOIN ttagartikel 
                    ON ttagartikel.kTag = ttag.kTag
                WHERE ttag.kSprache = " . (int)$_SESSION['kSprache'] . " 
                GROUP BY ttag.cName
                ORDER BY Anzahl DESC",
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($Tags as $tag) {
            if ($tag->cName !== $_POST['mapping_' . $tag->kTag]) {
                if (strlen($_POST['mapping_' . $tag->kTag]) > 0) {
                    $tagmapping_obj           = new stdClass();
                    $tagmapping_obj->kSprache = (int)$_SESSION['kSprache'];
                    $tagmapping_obj->cName    = $tag->cName;
                    $tagmapping_obj->cNameNeu = Shop::Container()->getDB()->escape($_POST['mapping_' . $tag->kTag]);

                    $Neuertag = Shop::Container()->getDB()->select('ttag', 'cName', $tagmapping_obj->cNameNeu);

                    if (isset($Neuertag->kTag) && $Neuertag->kTag > 0) {
                        Shop::Container()->getDB()->insert('ttagmapping', $tagmapping_obj);
                        Shop::Container()->getDB()->delete('ttag', 'kTag', $tag->kTag);
                        $upd = new stdClass();
                        $upd->kKey = (int)$Neuertag->kTag;
                        Shop::Container()->getDB()->update('tseo', ['cKey', 'kKey'], ['kTag', (int)$tag->kTag], $upd);
                        $tagmappings = Shop::Container()->getDB()->selectAll('ttagartikel', 'ktag', (int)$tag->kTag);

                        foreach ($tagmappings as $tagmapping) {
                            //update tab amount, delete product tagging with old tag ID
                            if (Shop::Container()->getDB()->query(
                                    "UPDATE ttagartikel 
                                        SET nAnzahlTagging = nAnzahlTagging+" . $tagmapping->nAnzahlTagging . "
                                        WHERE kTag = " . (int)$Neuertag->kTag . " 
                                            AND kArtikel = " . (int)$tagmapping->kArtikel,
                                    \DB\ReturnType::AFFECTED_ROWS
                                ) > 0
                            ) {
                                Shop::Container()->getDB()->delete(
                                    'ttagartikel',
                                    ['kTag', 'kArtikel'],
                                    [(int)$tag->kTag, (int)$tagmapping->kArtikel]
                                );
                            } else {
                                $upd = new stdClass();
                                $upd->kTag = (int)$Neuertag->kTag;
                                Shop::Container()->getDB()->update(
                                    'ttagartikel',
                                    ['kTag', 'kArtikel'],
                                    [(int)$tag->kTag, (int)$tagmapping->kArtikel],
                                    $upd
                                );
                            }
                        }
                        $cHinweis .= 'Der Tag "' . $tagmapping_obj->cName . '" wurde erfolgreich auf "' .
                            $tagmapping_obj->cNameNeu . '" gemappt.<br />';
                    }

                    unset($tagmapping_obj);
                }
            } else {
                $cHinweis .= 'Der Tag "' . $tag->cName . '" kann nicht auf den gleichen Tagbegriff gemappt werden.';
            }
        }
        $cHinweis .= 'Die Tags wurden erfolgreich aktualisiert.<br />';
    } elseif (isset($_POST['delete'])) { // Auswahl loeschen
        if (is_array($_POST['kTag'])) {
            //flush cache before deleting the tags, since they will be removed from ttagartikel
            flushAffectedArticleCache($_POST['kTag']);
            foreach ($_POST['kTag'] as $kTag) {
                $kTag = (int)$kTag;
                $oTag = Shop::Container()->getDB()->select('ttag', 'kTag', $kTag);
                if (strlen($oTag->cName) > 0) {
                    Shop::Container()->getDB()->query(
                        "DELETE ttag, tseo
                            FROM ttag
                            LEFT JOIN tseo 
                                ON tseo.cKey = 'kTag'
                                AND tseo.kKey = ttag.kTag
                            WHERE ttag.kTag = " . $kTag,
                        \DB\ReturnType::DEFAULT
                    );
                    //also delete possible mappings TO this tag
                    Shop::Container()->getDB()->delete('ttagmapping', 'cNameNeu', $oTag->cName);
                    Shop::Container()->getDB()->delete('ttagartikel', 'kTag', $kTag);
                    $cHinweis .= 'Der Tag "' . $oTag->cName . '" wurde erfolgreich gelöscht.<br />';
                } else {
                    $cFehler .= 'Es wurde kein Tag mit der ID "' . $kTag . '" gefunden.<br />';
                }
            }
        } else {
            $cFehler .= 'Bitte wählen Sie mindestens einen Tag aus.<br />';
        }
    }
} elseif (isset($_POST['tagging']) && (int)$_POST['tagging'] === 2 && FormHelper::validateToken()) { // Mappinglist
    if (isset($_POST['delete'])) {
        if (is_array($_POST['kTagMapping'])) {
            foreach ($_POST['kTagMapping'] as $kTagMapping) {
                $kTagMapping = (int)$kTagMapping;
                $oMapping    = Shop::Container()->getDB()->select('ttagmapping', 'kTagMapping', $kTagMapping);
                if (strlen($oMapping->cName) > 0) {
                    Shop::Container()->getDB()->delete('ttagmapping', 'kTagMapping', $kTagMapping);

                    $cHinweis .= 'Das Mapping "' . $oMapping->cName . '" wurde erfolgreich gelöscht.<br />';
                } else {
                    $cFehler .= 'Es wurde kein Mapping mit der ID "' . $kTagMapping . '" gefunden.<br />';
                }
            }
        } else {
            $cFehler .= 'Bitte wählen Sie mindestens ein Mapping aus.<br />';
        }
    }
} elseif ((isset($_POST['a']) && $_POST['a'] === 'saveSettings') ||
    (isset($_POST['tagging']) && (int)$_POST['tagging'] === 3)) { // Einstellungen
    $cHinweis .= saveAdminSettings($settingsIDs, $_POST);
}
// Tagdetail
if (RequestHelper::verifyGPCDataInt('kTag') > 0 && RequestHelper::verifyGPCDataInt('tagdetail') === 1) {
    $step = 'detail';
    // Pagination
    $nTagDetailAnzahl = holeTagDetailAnzahl(RequestHelper::verifyGPCDataInt('kTag'), $_SESSION['kSprache']);
    $oPagiTagDetail   = (new Pagination('detail'))
        ->setItemCount($nTagDetailAnzahl)
        ->assemble();
    // Tag von einem odere mehreren Artikeln loesen
    if (!empty($_POST['kArtikel_arr']) && is_array($_POST['kArtikel_arr']) &&
        count($_POST['kArtikel_arr']) && RequestHelper::verifyGPCDataInt('detailloeschen') === 1) {
        if (loescheTagsVomArtikel($_POST['kArtikel_arr'], RequestHelper::verifyGPCDataInt('kTag'))) {
            $cHinweis = 'Der Tag wurde erfolgreich bei Ihren markierten Artikeln gelöscht.';
        } else {
            $step    = 'detail';
            $cFehler = 'Fehler: Ihre markierten Artikel zum Produkttag konnten nicht gelöscht werden.';
        }
    }
    $oTagArtikel_arr = holeTagDetail(
        RequestHelper::verifyGPCDataInt('kTag'),
        (int)$_SESSION['kSprache'],
        ' LIMIT ' . $oPagiTagDetail->getLimitSQL()
    );
    $smarty->assign('oTagArtikel_arr', $oTagArtikel_arr)
        ->assign('oPagiTagDetail', $oPagiTagDetail)
        ->assign('kTag', RequestHelper::verifyGPCDataInt('kTag'))
        ->assign('cTagName', $oTagArtikel_arr[0]->cName ?? '');
} else {
    // Anzahl Tags fuer diese Sprache
    $nAnzahlTags = Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM ttag
            WHERE kSprache = " . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
    );
    // Anzahl Tag Mappings fuer diese Sprache
    $nAnzahlTagMappings = Shop::Container()->getDB()->query(
        "SELECT count(*) AS nAnzahl
            FROM ttagmapping
            WHERE kSprache = " . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
    );

    // Paginationen
    $oPagiTags = (new Pagination('tags'))
        ->setItemCount($nAnzahlTags->nAnzahl)
        ->assemble();
    $oPagiTagMappings = (new Pagination('mappings'))
        ->setItemCount($nAnzahlTagMappings->nAnzahl)
        ->assemble();

    $Sprachen = Sprache::getAllLanguages();
    $Tags     = Shop::Container()->getDB()->query(
        'SELECT ttag.kTag, ttag.cName, ttag.nAktiv, sum(ttagartikel.nAnzahlTagging) AS Anzahl 
            FROM ttag
            JOIN ttagartikel 
                ON ttagartikel.kTag = ttag.kTag
            WHERE ttag.kSprache = ' . (int)$_SESSION['kSprache'] . '
            GROUP BY ttag.cName
            ORDER BY Anzahl DESC
            LIMIT ' . $oPagiTags->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $Tagmapping = Shop::Container()->getDB()->query(
        'SELECT *
            FROM ttagmapping
            WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
            LIMIT ' . $oPagiTagMappings->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    // Config holen
    $oConfig_arr = Shop::Container()->getDB()->query(
        'SELECT *
            FROM teinstellungenconf
            WHERE kEinstellungenConf IN (' . implode(',', $settingsIDs) . ')
            ORDER BY nSort',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
    $configCount = count($oConfig_arr);
    for ($i = 0; $i < $configCount; $i++) {
        $oConfig_arr[$i]->ConfWerte = Shop::Container()->getDB()->selectAll(
            'teinstellungenconfwerte',
            'kEinstellungenConf',
            (int)$oConfig_arr[$i]->kEinstellungenConf,
            '*',
            'nSort'
        );
        $oSetValue = Shop::Container()->getDB()->select(
            'teinstellungen',
            'kEinstellungenSektion',
            (int)$oConfig_arr[$i]->kEinstellungenSektion,
            'cName',
            $oConfig_arr[$i]->cWertName
        );
        $oConfig_arr[$i]->gesetzterWert = $oSetValue->cWert ?? null;
    }

    $smarty->assign('oConfig_arr', $oConfig_arr)
           ->assign('oPagiTags', $oPagiTags)
           ->assign('oPagiTagMappings', $oPagiTagMappings)
           ->assign('Sprachen', $Sprachen)
           ->assign('Tags', $Tags)
           ->assign('Tagmapping', $Tagmapping);
}
$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('step', $step)
       ->display('tagging.tpl');
