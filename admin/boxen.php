<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use Helpers\Form;
use Helpers\Request;

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('BOXES_VIEW', true, true);
/** @global Smarty\JTLSmarty $smarty */

$cHinweis   = '';
$cFehler    = '';
$pageID     = Request::verifyGPCDataInt('page');
$boxService = Shop::Container()->getBoxService();
$boxAdmin   = new \Boxes\Admin\BoxAdmin(Shop::Container()->getDB());
$bOk        = false;
$linkID     = Request::verifyGPCDataInt('linkID');
$boxID      = Request::verifyGPCDataInt('item');
if (isset($_REQUEST['action']) && !isset($_REQUEST['revision-action']) && Form::validateToken()) {
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
            $ePosition  = $_REQUEST['position'];
            $kContainer = $_REQUEST['container'] ?? 0;
            if ($boxID === 0) {
                // Neuer Container
                $bOk = $boxAdmin->create(0, $pageID, $ePosition);
                if ($bOk) {
                    $cHinweis = 'Container wurde erfolgreich hinzugefügt.';
                } else {
                    $cFehler = 'Container konnte nicht angelegt werden.';
                }
            } else {
                $bOk = $boxAdmin->create($boxID, $pageID, $ePosition, $kContainer);
                if ($bOk) {
                    $cHinweis = 'Box wurde erfolgreich hinzugefügt.';
                } else {
                    $cFehler = 'Box konnte nicht angelegt werden.';
                }
            }
            break;

        case 'del':
            $bOk = $boxAdmin->delete($boxID);
            if ($bOk) {
                $cHinweis = 'Box wurde erfolgreich entfernt.';
            } else {
                $cFehler = 'Box konnte nicht entfernt werden.';
            }
            break;

        case 'edit_mode':
            $oBox = $boxAdmin->getByID($boxID);
            // revisions need this as a different formatted array
            $revisionData = [];
            foreach ($oBox->oSprache_arr as $lang) {
                $revisionData[$lang->cISO] = $lang;
            }
            $smarty->assign('oEditBox', $oBox)
                   ->assign('revisionData', $revisionData)
                   ->assign(
                       'oLink_arr',
                       Shop::Container()->getLinkService()->getAllLinkGroups()->filter(
                            function (\Link\LinkGroupInterface $e) {
                                return $e->isSpecial() === false;
                            }
                       )
                   );
            break;

        case 'edit':
            $cTitel = $_REQUEST['boxtitle'];
            $eTyp   = $_REQUEST['typ'];
            if ($eTyp === 'text') {
                $oldBox = $boxAdmin->getByID($boxID);
                if ($oldBox->supportsRevisions === true) {
                    $revision = new Revision();
                    $revision->addRevision('box', $boxID, true);
                }
                $bOk = $boxAdmin->update($boxID, $cTitel);
                if ($bOk) {
                    foreach ($_REQUEST['title'] as $cISO => $cTitel) {
                        $cInhalt = $_REQUEST['text'][$cISO];
                        $bOk     = $boxAdmin->updateLanguage($boxID, $cISO, $cTitel, $cInhalt);
                        if (!$bOk) {
                            break;
                        }
                    }
                }
            } elseif (($eTyp === \Boxes\Type::LINK && $linkID > 0) || $eTyp === \Boxes\Type::CATBOX) {
                $bOk = $boxAdmin->update($boxID, $cTitel, $linkID);
                if ($bOk) {
                    foreach ($_REQUEST['title'] as $cISO => $cTitel) {
                        $bOk = $boxAdmin->updateLanguage($boxID, $cISO, $cTitel, '');
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
            $ePosition = $_REQUEST['position'];
            $boxes     = $_REQUEST['box'] ?? [];
            $sort_arr  = $_REQUEST['sort'] ?? [];
            $aktiv_arr = $_REQUEST['aktiv'] ?? [];
            $boxCount  = count($boxes);
            $bValue    = $_REQUEST['box_show'] ?? false;
            $bOk       = $boxAdmin->setVisibility($pageID, $ePosition, $bValue);
            if ($bOk) {
                $cHinweis = 'Box wurde erfolgreich bearbeitet.';
            } else {
                $cFehler = 'Box konnte nicht bearbeitet werden.';
            }

            foreach ($boxes as $i => $box) {
                $idx = 'box-filter-' . $box;
                $boxAdmin->sort($box, $pageID, $sort_arr[$i], in_array($box, $aktiv_arr));
                $boxAdmin->filterBoxVisibility((int)$box, $pageID, $_POST[$idx] ?? '');
            }
            // see jtlshop/jtl-shop/issues#544 && jtlshop/shop4#41
            if ($ePosition !== 'left' || $pageID > 0) {
                $boxAdmin->setVisibility($pageID, $ePosition, isset($_REQUEST['box_show']));
            }
            $cHinweis = 'Die Boxen wurden aktualisiert.';
            break;

        case 'activate':
            $bActive = (bool)$_REQUEST['value'];
            $bOk     = $boxAdmin->activate($boxID, 0, $bActive);
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
    $flushres = Shop::Container()->getCache()->flushTags([CACHING_GROUP_OBJECT, CACHING_GROUP_BOX, 'boxes']);
    Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', \DB\ReturnType::DEFAULT);
}
$oBoxen_arr      = $boxService->buildList($pageID, false, true);
$oVorlagen_arr   = $boxAdmin->getTemplates($pageID);
$oBoxenContainer = Template::getInstance()->getBoxLayoutXML();
$filterMapping   = [];
if ($pageID === PAGE_ARTIKELLISTE) { //map category name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kKategorie AS id, cName AS name FROM tkategorie',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
} elseif ($pageID === PAGE_ARTIKEL) { //map article name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kArtikel AS id, cName AS name FROM tartikel',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
} elseif ($pageID === PAGE_HERSTELLER) { //map manufacturer name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kHersteller AS id, cName AS name FROM thersteller',
        \DB\ReturnType::ARRAY_OF_OBJECTS
    );
} elseif ($pageID === PAGE_EIGENE) { //map page name
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
       ->assign('bBoxenAnzeigen', $boxAdmin->getVisibility($pageID))
       ->assign('oBoxenLeft_arr', $oBoxen_arr['left'] ?? [])
       ->assign('oBoxenTop_arr', $oBoxen_arr['top'] ?? [])
       ->assign('oBoxenBottom_arr', $oBoxen_arr['bottom'] ?? [])
       ->assign('oBoxenRight_arr', $oBoxen_arr['right'] ?? [])
       ->assign('oContainerTop_arr', $boxAdmin->getContainer('top'))
       ->assign('oContainerBottom_arr', $boxAdmin->getContainer('bottom'))
       ->assign('oSprachen_arr', Shop::Lang()->getAvailable())
       ->assign('oVorlagen_arr', $oVorlagen_arr)
       ->assign('oBoxenContainer', $oBoxenContainer)
       ->assign('nPage', $pageID)
       ->assign('invisibleBoxes', $boxAdmin->getInvisibleBoxes())
       ->display('boxen.tpl');
