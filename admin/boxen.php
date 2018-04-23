<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('BOXES_VIEW', true, true);
/** @global JTLSmarty $smarty */

$cHinweis = '';
$cFehler  = '';
$nPage    = 0;
$oBoxen   = Boxen::getInstance();
$bOk      = false;

if (isset($_REQUEST['page'])) {
    $nPage = (int)$_REQUEST['page'];
}
if (isset($_REQUEST['action']) && !isset($_REQUEST['revision-action']) && validateToken()) {
    switch ($_REQUEST['action']) {
        case 'delete-invisible':
            if (!empty($_POST['kInvisibleBox']) && count($_POST['kInvisibleBox']) > 0) {
                $cnt = 0;
                foreach ($_POST['kInvisibleBox'] as $box) {
                    $bOk = $oBoxen->loescheBox((int)$box);
                    if ($box) {
                        ++$cnt;
                    }
                }
                $cHinweis = $cnt . ' Box(en) wurde(n) erfolgreich gel&ouml;scht.';
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
                    $bOk = $oBoxen->setzeBox(0, $nPage, $ePosition);
                    if ($bOk) {
                        $cHinweis = 'Container wurde erfolgreich hinzugef&uuml;gt.';
                    } else {
                        $cFehler = 'Container konnte nicht angelegt werden.';
                    }
                } else {
                    $bOk = $oBoxen->setzeBox($kBox, $nPage, $ePosition, $kContainer);
                    if ($bOk) {
                        $cHinweis = 'Box wurde erfolgreich hinzugef&uuml;gt.';
                    } else {
                        $cFehler = 'Box konnte nicht angelegt werden.';
                    }
                }
            }
            break;

        case 'del':
            $kBox = (int)$_REQUEST['item'];
            $bOk  = $oBoxen->loescheBox($kBox);
            if ($bOk) {
                $cHinweis = 'Box wurde erfolgreich entfernt.';
            } else {
                $cFehler = 'Box konnte nicht entfernt werden.';
            }
            break;

        case 'edit_mode':
            $kBox = (int)$_REQUEST['item'];
            $oBox = $oBoxen->holeBox($kBox);
            // revisions need this as a different formatted array
            $revisionData = [];
            foreach ($oBox->oSprache_arr as $lang) {
                $revisionData[$lang->cISO] = $lang;
            }
            $smarty->assign('oEditBox', $oBox)
                   ->assign('revisionData', $revisionData)
                   ->assign('oLink_arr', $oBoxen->gibLinkGruppen());
            break;

        case 'edit':
            $kBox   = (int)$_REQUEST['item'];
            $cTitel = $_REQUEST['boxtitle'];
            $eTyp   = $_REQUEST['typ'];
            if ($eTyp === 'text') {
                $oldBox = $oBoxen->holeBox($kBox);
                if ($oldBox->supportsRevisions === true) {
                    $revision = new Revision();
                    $revision->addRevision('box', $kBox, true);
                }
                $bOk = $oBoxen->bearbeiteBox($kBox, $cTitel);
                if ($bOk) {
                    foreach ($_REQUEST['title'] as $cISO => $cTitel) {
                        $cInhalt = $_REQUEST['text'][$cISO];
                        $bOk     = $oBoxen->bearbeiteBoxSprache($kBox, $cISO, $cTitel, $cInhalt);
                        if (!$bOk) {
                            break;
                        }
                    }
                }
            } elseif ($eTyp === 'link') {
                $linkID = (int)$_REQUEST['linkID'];
                if ($linkID > 0) {
                    $bOk = $oBoxen->bearbeiteBox($kBox, $cTitel, $linkID);
                }
            } elseif ($eTyp === 'catbox') {
                $linkID = (int)$_REQUEST['linkID'];
                $bOk    = $oBoxen->bearbeiteBox($kBox, $cTitel, $linkID);
                if ($bOk) {
                    foreach ($_REQUEST['title'] as $cISO => $cTitel) {
                        $bOk = $oBoxen->bearbeiteBoxSprache($kBox, $cISO, $cTitel, '');
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
            $bValue    = $_REQUEST['box'] ?? false;
            $bOk       = $oBoxen->setzeBoxAnzeige($nPage, $ePosition, $bValue);
            if ($bOk) {
                $cHinweis = 'Box wurde erfolgreich bearbeitet.';
            } else {
                $cFehler = 'Box konnte nicht bearbeitet werden.';
            }

            foreach ($box_arr as $i => $kBox) {
                $idx = 'box-filter-' . $kBox;
                $oBoxen->sortBox($kBox, $nPage, $sort_arr[$i], in_array($kBox, $aktiv_arr));
                $oBoxen->filterBoxVisibility((int)$kBox, $nPage, $_POST[$idx] ?? '');
            }
            // see jtlshop/jtl-shop/issues#544 && jtlshop/shop4#41
            if ($ePosition !== 'left' || $nPage > 0) {
                $oBoxen->setzeBoxAnzeige($nPage, $ePosition, isset($_REQUEST['box_show']));
            }
            $cHinweis = 'Die Boxen wurden aktualisiert.';
            break;

        case 'activate':
            $kBox    = (int)$_REQUEST['item'];
            $bActive = (boolean)$_REQUEST['value'];
            $bOk     = $oBoxen->aktiviereBox($kBox, 0, $bActive);
            if ($bOk) {
                $cHinweis = 'Box wurde erfolgreich bearbeitet.';
            } else {
                $cFehler = 'Box konnte nicht bearbeitet werden.';
            }
            break;

        case 'container':
            $ePosition = $_REQUEST['position'];
            $bValue    = (boolean)$_GET['value'];
            $bOk       = $oBoxen->setzeBoxAnzeige(0, $ePosition, $bValue);
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
    Shop::Container()->getDB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
}
$oBoxen_arr      = $oBoxen->holeBoxen($nPage, false, true, true);
$oVorlagen_arr   = $oBoxen->holeVorlagen($nPage);
$oBoxenContainer = Template::getInstance()->getBoxLayoutXML();

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('bBoxenAnzeigen', $oBoxen->holeBoxAnzeige($nPage))
       ->assign('oBoxenLeft_arr', $oBoxen_arr['left'] ?? null)
       ->assign('oBoxenTop_arr', $oBoxen_arr['top'] ?? null)
       ->assign('oBoxenBottom_arr', $oBoxen_arr['bottom'] ?? null)
       ->assign('oBoxenRight_arr', $oBoxen_arr['right'] ?? null)
       ->assign('oContainerTop_arr', $oBoxen->holeContainer('top'))
       ->assign('oContainerBottom_arr', $oBoxen->holeContainer('bottom'))
       ->assign('oSprachen_arr', Shop::Lang()->getAvailable())
       ->assign('oVorlagen_arr', $oVorlagen_arr)
       ->assign('oBoxenContainer', $oBoxenContainer)
       ->assign('nPage', $nPage)
       ->assign('invisibleBoxes', $oBoxen->getInvisibleBoxes())
       ->display('boxen.tpl');
