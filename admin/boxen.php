<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('BOXES_VIEW', true, true);
/** @global JTLSmarty $smarty */

$cHinweis   = '';
$cFehler    = '';
$nPage      = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 0;
$boxService = Shop::Container()->getBoxService();
$boxAdmin   = new \Boxes\Admin\BoxAdmin(Shop::Container()->getDB(), $boxService);
$bOk        = false;
if (isset($_REQUEST['action']) && !isset($_REQUEST['revision-action']) && FormHelper::validateToken()) {
    switch ($_REQUEST['action']) {
        case 'delete-invisible':
            if (!empty($_POST['kInvisibleBox']) && count($_POST['kInvisibleBox']) > 0) {
                $cnt = 0;
                foreach ($_POST['kInvisibleBox'] as $box) {
                    $bOk = $boxAdmin->delete((int)$box);
                    if ($box) {
                        ++$cnt;
                    }
                }
                $cHinweis = $cnt . ' Box(en) wurde(n) erfolgreich gelöscht.';
            }
            break;

        case 'new':
            $kBox       = $_REQUEST['item'];
            $ePosition  = $_REQUEST['position'];
            $kContainer = $_REQUEST['container'] ?? 0;
            if (is_numeric($kBox)) {
                $kBox = (int)$kBox;
                if ($kBox === 0) {
                    // Neuer Container
                    $bOk = $boxAdmin->create(0, $nPage, $ePosition);
                    if ($bOk) {
                        $cHinweis = 'Container wurde erfolgreich hinzugefügt.';
                    } else {
                        $cFehler = 'Container konnte nicht angelegt werden.';
                    }
                } else {
                    $bOk = $boxAdmin->create($kBox, $nPage, $ePosition, $kContainer);
                    if ($bOk) {
                        $cHinweis = 'Box wurde erfolgreich hinzugefügt.';
                    } else {
                        $cFehler = 'Box konnte nicht angelegt werden.';
                    }
                }
            }
            break;

        case 'del':
            $kBox = (int)$_REQUEST['item'];
            $bOk  = $boxAdmin->delete($kBox);
            if ($bOk) {
                $cHinweis = 'Box wurde erfolgreich entfernt.';
            } else {
                $cFehler = 'Box konnte nicht entfernt werden.';
            }
            break;

        case 'edit_mode':
            $kBox = (int)$_REQUEST['item'];
            $oBox = $boxAdmin->getByID($kBox);
            // revisions need this as a different formatted array
            $revisionData = [];
            foreach ($oBox->oSprache_arr as $lang) {
                $revisionData[$lang->cISO] = $lang;
            }
            $smarty->assign('oEditBox', $oBox)
                   ->assign('revisionData', $revisionData)
                   ->assign('oLink_arr',
                       Shop::Container()->getDB()->query("SELECT * FROM tlinkgruppe", \DB\ReturnType::ARRAY_OF_OBJECTS)
                   );
            break;

        case 'edit':
            $kBox   = (int)$_REQUEST['item'];
            $cTitel = $_REQUEST['boxtitle'];
            $eTyp   = $_REQUEST['typ'];
            if ($eTyp === 'text') {
                $oldBox = $boxAdmin->getByID($kBox);
                if ($oldBox->supportsRevisions === true) {
                    $revision = new Revision();
                    $revision->addRevision('box', $kBox, true);
                }
                $bOk = $boxAdmin->update($kBox, $cTitel);
                if ($bOk) {
                    foreach ($_REQUEST['title'] as $cISO => $cTitel) {
                        $cInhalt = $_REQUEST['text'][$cISO];
                        $bOk     = $boxAdmin->updateLanguage($kBox, $cISO, $cTitel, $cInhalt);
                        if (!$bOk) {
                            break;
                        }
                    }
                }
            } elseif ($eTyp === \Boxes\BoxType::LINK) {
                $linkID = (int)$_REQUEST['linkID'];
                if ($linkID > 0) {
                    $bOk = $boxAdmin->update($kBox, $cTitel, $linkID);
                }
            } elseif ($eTyp === \Boxes\BoxType::CATBOX) {
                $linkID = (int)$_REQUEST['linkID'];
                $bOk    = $boxAdmin->update($kBox, $cTitel, $linkID);
                if ($bOk) {
                    foreach ($_REQUEST['title'] as $cISO => $cTitel) {
                        $bOk = $boxAdmin->updateLanguage($kBox, $cISO, $cTitel, '');
                        if (!$bOk) {
                            break;
                        }
                    }
                }
            }

            if ($bOk) {
                $cHinweis = 'Box wurde erfolgreich bearbeitet.';
            } else {
                $cFehler = 'Box konnte nicht bearbeitet werden.';
            }
            break;

        case 'resort':
            $nPage     = (int)$_REQUEST['page'];
            $ePosition = $_REQUEST['position'];
            $box_arr   = $_REQUEST['box'] ?? null;
            $sort_arr  = $_REQUEST['sort'] ?? null;
            $aktiv_arr = $_REQUEST['aktiv'] ?? [];
            $boxCount  = count($box_arr);
            $bValue    = $_REQUEST['box_show'] ?? false;
            $bOk       = $boxAdmin->setVisibility($nPage, $ePosition, $bValue);
            if ($bOk) {
                $cHinweis = 'Box wurde erfolgreich bearbeitet.';
            } else {
                $cFehler = 'Box konnte nicht bearbeitet werden.';
            }

            foreach ($box_arr as $i => $kBox) {
                $idx = 'box-filter-' . $kBox;
                $boxAdmin->sort($kBox, $nPage, $sort_arr[$i], in_array($kBox, $aktiv_arr));
                $boxAdmin->filterBoxVisibility((int)$kBox, $nPage, $_POST[$idx] ?? '');
            }
            // see jtlshop/jtl-shop/issues#544 && jtlshop/shop4#41
            if ($ePosition !== 'left' || $nPage > 0) {
                $boxAdmin->setVisibility($nPage, $ePosition, isset($_REQUEST['box_show']));
            }
            $cHinweis = 'Die Boxen wurden aktualisiert.';
            break;

        case 'activate':
            $kBox    = (int)$_REQUEST['item'];
            $bActive = (bool)$_REQUEST['value'];
            $bOk     = $boxAdmin->activate($kBox, 0, $bActive);
            if ($bOk) {
                $cHinweis = 'Box wurde erfolgreich bearbeitet.';
            } else {
                $cFehler = 'Box konnte nicht bearbeitet werden.';
            }
            break;

        case 'container':
            $ePosition = $_REQUEST['position'];
            $bValue    = (bool)$_GET['value'];
            $bOk       = $boxAdmin->setVisibility(0, $ePosition, $bValue);
            if ($bOk) {
                $cHinweis = 'Box wurde erfolgreich bearbeitet.';
            } else {
                $cFehler = 'Box konnte nicht bearbeitet werden.';
            }
            break;

        default:
            break;
    }
    $flushres = Shop::Cache()->flushTags([CACHING_GROUP_OBJECT, CACHING_GROUP_BOX, 'boxes']);
    Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = now()', \DB\ReturnType::DEFAULT);
}
$oBoxen_arr      = $boxService->buildList($nPage, false, true);
$oVorlagen_arr   = $boxAdmin->getTemplates($nPage);
$oBoxenContainer = Template::getInstance()->getBoxLayoutXML();
$filterMapping   = [];
if ($nPage === PAGE_ARTIKELLISTE) { //map category name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kKategorie AS id, cName AS name FROM tkategorie',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
} elseif ($nPage === PAGE_ARTIKEL) { //map article name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kArtikel AS id, cName AS name FROM tartikel',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
} elseif ($nPage === PAGE_HERSTELLER) { //map manufacturer name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kHersteller AS id, cName AS name FROM thersteller',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
} elseif ($nPage === PAGE_EIGENE) { //map page name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kLink AS id, cName AS name FROM tlink',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
}

$filterMapping = \Functional\reindex($filterMapping, function ($e) {
    return $e->id;
});
$filterMapping = \Functional\map($filterMapping, function ($e) {
    return $e->name;
});
$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('filterMapping', $filterMapping)
       ->assign('validPageTypes', $boxAdmin->getValidPageTypes())
       ->assign('bBoxenAnzeigen', $boxAdmin->getVisibility($nPage))
       ->assign('oBoxenLeft_arr', $oBoxen_arr['left'] ?? [])
       ->assign('oBoxenTop_arr', $oBoxen_arr['top'] ?? [])
       ->assign('oBoxenBottom_arr', $oBoxen_arr['bottom'] ?? [])
       ->assign('oBoxenRight_arr', $oBoxen_arr['right'] ?? [])
       ->assign('oContainerTop_arr', $boxAdmin->getContainer('top'))
       ->assign('oContainerBottom_arr', $boxAdmin->getContainer('bottom'))
       ->assign('oSprachen_arr', Shop::Lang()->getAvailable())
       ->assign('oVorlagen_arr', $oVorlagen_arr)
       ->assign('oBoxenContainer', $oBoxenContainer)
       ->assign('nPage', $nPage)
       ->assign('invisibleBoxes', $boxAdmin->getInvisibleBoxes())
       ->display('boxen.tpl');
