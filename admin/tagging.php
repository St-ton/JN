<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('MODULE_PRODUCTTAGS_VIEW', true, true);

require_once PFAD_ROOT . PFAD_DBES . 'seo.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'tagging_inc.php';
/** @global Smarty\JTLSmarty $smarty */
setzeSprache();

$cHinweis          = '';
$cFehler           = '';
$step              = 'uebersicht';
$settingsIDs       = [427, 428, 431, 433, 434, 435, 430];
$db                = Shop::Container()->getDB();
if (strlen(RequestHelper::verifyGPDataString('tab')) > 0) {
    $smarty->assign('cTab', RequestHelper::verifyGPDataString('tab'));
}
if (isset($_POST['tagging']) && (int)$_POST['tagging'] === 1 && FormHelper::validateToken()) {
    if (!isset($_POST['delete'])) {
        if (is_array($_POST['kTagAll']) && count($_POST['kTagAll']) > 0) {
            $cSQLDel = ' IN (';
            foreach ($_POST['kTagAll'] as $i => $kTagAll) {
                $upd         = new stdClass();
                $upd->nAktiv = 0;
                $db->update('ttag', 'kTag', (int)$kTagAll, $upd);
                // Loeschequery vorbereiten
                if ($i > 0) {
                    $cSQLDel .= ', ' . (int)$kTagAll;
                } else {
                    $cSQLDel .= (int)$kTagAll;
                }
            }
            $cSQLDel .= ')';
            // Deaktivierten Tag aus tseo loeschen
            $db->query(
                "DELETE FROM tseo
                    WHERE cKey = 'kTag'
                        AND kKey" . $cSQLDel,
                \DB\ReturnType::AFFECTED_ROWS
            );
            // Deaktivierten Tag in ttag updaten
            $db->query(
                "UPDATE ttag
                    SET cSeo = ''
                    WHERE kTag" . $cSQLDel,
                \DB\ReturnType::AFFECTED_ROWS
            );
            // nAktiv Reihe updaten
            if (is_array($_POST['nAktiv'])) {
                foreach ($_POST['nAktiv'] as $i => $nAktiv) {
                    $oTag = $db->select('ttag', 'kTag', (int)$nAktiv);
                    $db->delete(
                        'tseo',
                        ['cKey', 'kKey', 'kSprache'],
                        ['kTag', (int)$nAktiv, (int)$_SESSION['kSprache']]
                    );
                    // Aktivierten Tag in tseo eintragen
                    $oSeo           = new stdClass();
                    $oSeo->cSeo     = isset($oTag->cName)
                        ? \JTL\SeoHelper::checkSeo(\JTL\SeoHelper::getSeo($oTag->cName))
                        : '';
                    $oSeo->cKey     = 'kTag';
                    $oSeo->kKey     = $nAktiv;
                    $oSeo->kSprache = $_SESSION['kSprache'];
                    $db->insert('tseo', $oSeo);
                    // Aktivierte Suchanfragen in tsuchanfrage updaten
                    $upd         = new stdClass();
                    $upd->nAktiv = 1;
                    $upd->cSeo   = $oSeo->cSeo;
                    $db->update('ttag', 'kTag', $nAktiv, $upd);
                }
            }
            flushAffectedArticleCache($_POST['kTagAll']);
        }
        // Eintragen in die Mapping Tabelle
        $Tags = $db->query(
            'SELECT ttag.kTag, ttag.cName, ttag.nAktiv, sum(ttagartikel.nAnzahlTagging) AS Anzahl 
                FROM ttag
                JOIN ttagartikel 
                    ON ttagartikel.kTag = ttag.kTag
                WHERE ttag.kSprache = ' . (int)$_SESSION['kSprache'] . ' 
                GROUP BY ttag.cName
                ORDER BY Anzahl DESC',
            \DB\ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($Tags as $tag) {
            if ($tag->cName !== $_POST['mapping_' . $tag->kTag]) {
                if (strlen($_POST['mapping_' . $tag->kTag]) > 0) {
                    $tagmapping_obj           = new stdClass();
                    $tagmapping_obj->kSprache = (int)$_SESSION['kSprache'];
                    $tagmapping_obj->cName    = $tag->cName;
                    $tagmapping_obj->cNameNeu = $db->escape($_POST['mapping_' . $tag->kTag]);

                    $Neuertag = $db->select('ttag', 'cName', $tagmapping_obj->cNameNeu);

                    if (isset($Neuertag->kTag) && $Neuertag->kTag > 0) {
                        $db->insert('ttagmapping', $tagmapping_obj);
                        $db->delete('ttag', 'kTag', $tag->kTag);
                        $upd = new stdClass();
                        $upd->kKey = (int)$Neuertag->kTag;
                        $db->update('tseo', ['cKey', 'kKey'], ['kTag', (int)$tag->kTag], $upd);
                        $tagmappings = $db->selectAll('ttagartikel', 'ktag', (int)$tag->kTag);

                        foreach ($tagmappings as $tagmapping) {
                            //update tab amount, delete product tagging with old tag ID
                            if ($db->query(
                                'UPDATE ttagartikel 
                                    SET nAnzahlTagging = nAnzahlTagging + ' . $tagmapping->nAnzahlTagging . '
                                    WHERE kTag = ' . (int)$Neuertag->kTag . ' 
                                        AND kArtikel = ' . (int)$tagmapping->kArtikel,
                                \DB\ReturnType::AFFECTED_ROWS
                            ) > 0) {
                                $db->delete(
                                    'ttagartikel',
                                    ['kTag', 'kArtikel'],
                                    [(int)$tag->kTag, (int)$tagmapping->kArtikel]
                                );
                            } else {
                                $upd = new stdClass();
                                $upd->kTag = (int)$Neuertag->kTag;
                                $db->update(
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
                $oTag = $db->select('ttag', 'kTag', $kTag);
                if (strlen($oTag->cName) > 0) {
                    $db->query(
                        "DELETE ttag, tseo
                            FROM ttag
                            LEFT JOIN tseo 
                                ON tseo.cKey = 'kTag'
                                AND tseo.kKey = ttag.kTag
                            WHERE ttag.kTag = " . $kTag,
                        \DB\ReturnType::DEFAULT
                    );
                    //also delete possible mappings TO this tag
                    $db->delete('ttagmapping', 'cNameNeu', $oTag->cName);
                    $db->delete('ttagartikel', 'kTag', $kTag);
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
                $oMapping    = $db->select('ttagmapping', 'kTagMapping', $kTagMapping);
                if (strlen($oMapping->cName) > 0) {
                    $db->delete('ttagmapping', 'kTagMapping', $kTagMapping);

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
    $nAnzahlTags        = $db->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM ttag
            WHERE kSprache = ' . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
    );
    $nAnzahlTagMappings = $db->query(
        'SELECT COUNT(*) AS nAnzahl
            FROM ttagmapping
            WHERE kSprache = ' . (int)$_SESSION['kSprache'],
        \DB\ReturnType::SINGLE_OBJECT
    );

    $oPagiTags = (new Pagination('tags'))
        ->setItemCount($nAnzahlTags->nAnzahl)
        ->assemble();
    $oPagiTagMappings = (new Pagination('mappings'))
        ->setItemCount($nAnzahlTagMappings->nAnzahl)
        ->assemble();

    $Sprachen = Sprache::getAllLanguages();
    $Tags     = $db->query(
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
    $Tagmapping = $db->query(
        'SELECT *
            FROM ttagmapping
            WHERE kSprache = ' . (int)$_SESSION['kSprache'] . '
            LIMIT ' . $oPagiTagMappings->getLimitSQL(),
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );

    $smarty->assign('oConfig_arr', getAdminSectionSettings($settingsIDs))
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
