<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'template_inc.php';

$oAccount->permission('BOXES_VIEW', true, true);

$oTemplate = Template::getInstance();
$cHinweis  = '';
$cFehler   = '';
$nPage     = 0;
$oBoxen    = Boxen::getInstance();
$bOk       = false;

if (isset($_REQUEST['page'])) {
    $nPage = (int)$_REQUEST['page'];
}
if (isset($_REQUEST['action']) && validateToken()) {
    switch ($_REQUEST['action']) {
        case 'new':
            $kBox       = $_REQUEST['item'];
            $ePosition  = $_REQUEST['position'];
            $kContainer = (isset($_REQUEST['container']) ? $_REQUEST['container'] : 0);
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
            $smarty->assign('oEditBox', $oBox)
                   ->assign('oLink_arr', $oBoxen->gibLinkGruppen());
            break;

        case 'edit':
            $kBox   = (int)$_REQUEST['item'];
            $cTitel = $_REQUEST['boxtitle'];
            $eTyp   = $_REQUEST['typ'];
            if ($eTyp === 'text') {
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
            $nPage     = $_REQUEST['page'];
            $ePosition = $_REQUEST['position'];
            $box_arr   = (isset($_REQUEST['box'])) ? $_REQUEST['box'] : null;
            $sort_arr  = (isset($_REQUEST['sort'])) ? $_REQUEST['sort'] : null;
            $aktiv_arr = (isset($_REQUEST['aktiv'])) ? $_REQUEST['aktiv'] : null;
            $boxCount  = count($box_arr);
            for ($i = 0; $i < $boxCount; $i++) {
                $oBoxen->sortBox($box_arr[$i], $nPage, $sort_arr[$i], @in_array($box_arr[$i], $aktiv_arr) ? true : false);
                $oBoxen->filterBoxVisibility((int)$box_arr[$i], (int)$nPage, (isset($_POST['box-filter-' . $box_arr[$i]])) ? $_POST['box-filter-' . $box_arr[$i]] : '');
            }
            // see jtlshop/jtl-shop/issues#544
            if ((int)$nPage > 0) {
                $oBoxen->setzeBoxAnzeige($nPage, $ePosition, isset($_REQUEST['box_show']));
            }
            $cHinweis = 'Die Boxen wurden aktualisiert.';
            break;

        case 'activate':
            $kBox    = (int)$_REQUEST['item'];
            $bActive = (boolean) intval($_REQUEST['value']);
            $bOk     = $oBoxen->aktiviereBox($kBox, 0, $bActive);
            if ($bOk) {
                $cHinweis = 'Box wurde erfolgreich bearbeitet.';
            } else {
                $cFehler = 'Box konnte nicht bearbeitet werden.';
            }
            break;

        case 'container':
            $ePosition = $_REQUEST['position'];
            $bValue    = (boolean) intval($_GET['value']);
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
    $flushres = Shop::Cache()->flushTags(array(CACHING_GROUP_OBJECT, CACHING_GROUP_BOX, 'boxes'));
    Shop::DB()->query("UPDATE tglobals SET dLetzteAenderung = now()", 4);
}
$oBoxen_arr      = $oBoxen->holeBoxen($nPage, false, true, true);
$oVorlagen_arr   = $oBoxen->holeVorlagen($nPage);
$oBoxenContainer = $oTemplate->getBoxLayoutXML();

$smarty->assign('hinweis', $cHinweis)
       ->assign('fehler', $cFehler)
       ->assign('bBoxenAnzeigen', $oBoxen->holeBoxAnzeige($nPage))
       ->assign('oBoxenLeft_arr', (isset($oBoxen_arr['left'])) ? $oBoxen_arr['left'] : null)
       ->assign('oBoxenTop_arr', (isset($oBoxen_arr['top']) ? $oBoxen_arr['top'] : null))
       ->assign('oBoxenBottom_arr', (isset($oBoxen_arr['bottom']) ? $oBoxen_arr['bottom'] : null))
       ->assign('oBoxenRight_arr', (isset($oBoxen_arr['right'])) ? $oBoxen_arr['right'] : null)
       ->assign('oContainerTop_arr', $oBoxen->holeContainer('top'))
       ->assign('oContainerBottom_arr', $oBoxen->holeContainer('bottom'))
       ->assign('oSprachen_arr', Shop::Lang()->getAvailable())
       ->assign('oVorlagen_arr', $oVorlagen_arr)
       ->assign('oBoxenContainer', $oBoxenContainer)
       ->assign('nPage', $nPage)
       ->display('boxen.tpl');
