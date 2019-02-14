<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Backend\Revision;
use JTL\Boxes\Admin\BoxAdmin;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Shop;
use JTL\Template;
use JTL\DB\ReturnType;
use JTL\Link\LinkGroupInterface;
use JTL\Boxes\Type;

require_once __DIR__ . '/includes/admininclude.php';
$oAccount->permission('BOXES_VIEW', true, true);
/** @global \JTL\Smarty\JTLSmarty $smarty */

$pageID      = Request::verifyGPCDataInt('page');
$boxService  = Shop::Container()->getBoxService();
$boxAdmin    = new BoxAdmin(Shop::Container()->getDB());
$bOk         = false;
$linkID      = Request::verifyGPCDataInt('linkID');
$boxID       = Request::verifyGPCDataInt('item');
$alertHelper = Shop::Container()->getAlertService();

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
                $alertHelper->addAlert(Alert::TYPE_NOTE, $cnt . __('successBoxDelete'), 'successBoxDelete');
            }
            break;

        case 'new':
            $ePosition  = $_REQUEST['position'];
            $kContainer = $_REQUEST['container'] ?? 0;
            if ($boxID === 0) {
                // Neuer Container
                $bOk = $boxAdmin->create(0, $pageID, $ePosition);
                if ($bOk) {
                    $alertHelper->addAlert(Alert::TYPE_NOTE, __('successContainerCreate'), 'successContainerCreate');
                } else {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorContainerCreate'), 'errorContainerCreate');
                }
            } else {
                $bOk = $boxAdmin->create($boxID, $pageID, $ePosition, $kContainer);
                if ($bOk) {
                    $alertHelper->addAlert(Alert::TYPE_NOTE, __('successBoxCreate'), 'successBoxCreate');
                } else {
                    $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBoxCreate'), 'errorBoxCreate');
                }
            }
            break;

        case 'del':
            $bOk = $boxAdmin->delete($boxID);
            if ($bOk) {
                $alertHelper->addAlert(Alert::TYPE_NOTE, __('successBoxDelete'), 'successBoxDelete');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBoxDelete'), 'errorBoxDelete');
            }
            break;

        case 'edit_mode':
            $oBox = $boxAdmin->getByID($boxID);
            // revisions need this as a different formatted array
            $revisionData = [];
            foreach ($oBox->oSprache_arr as $lang) {
                $revisionData[$lang->cISO] = $lang;
            }
            $links = Shop::Container()->getLinkService()->getAllLinkGroups()->filter(
                function (LinkGroupInterface $e) {
                    return $e->isSpecial() === false;
                }
            );
            $smarty->assign('oEditBox', $oBox)
                   ->assign('revisionData', $revisionData)
                   ->assign('oLink_arr', $links);
            break;

        case 'edit':
            $cTitel = $_REQUEST['boxtitle'];
            $eTyp   = $_REQUEST['typ'];
            if ($eTyp === 'text') {
                $oldBox = $boxAdmin->getByID($boxID);
                if ($oldBox->supportsRevisions === true) {
                    $revision = new Revision(Shop::Container()->getDB());
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
            } elseif (($eTyp === Type::LINK && $linkID > 0) || $eTyp === Type::CATBOX) {
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
                $alertHelper->addAlert(Alert::TYPE_NOTE, __('successBoxEdit'), 'successBoxEdit');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBoxEdit'), 'errorBoxEdit');
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
                $alertHelper->addAlert(Alert::TYPE_NOTE, __('successBoxEdit'), 'successBoxEdit');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBoxEdit'), 'errorBoxEdit');
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
            $alertHelper->addAlert(Alert::TYPE_NOTE, __('successBoxRefresh'), 'successBoxRefresh');
            break;

        case 'activate':
            $bActive = (bool)$_REQUEST['value'];
            $bOk     = $boxAdmin->activate($boxID, 0, $bActive);
            if ($bOk) {
                $alertHelper->addAlert(Alert::TYPE_NOTE, __('successBoxEdit'), 'successBoxEdit');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBoxEdit'), 'errorBoxEdit');
            }
            break;

        case 'container':
            $ePosition = $_REQUEST['position'];
            $bValue    = (bool)$_GET['value'];
            $bOk       = $boxAdmin->setVisibility(0, $ePosition, $bValue);
            if ($bOk) {
                $alertHelper->addAlert(Alert::TYPE_NOTE, __('successBoxEdit'), 'successBoxEdit');
            } else {
                $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorBoxEdit'), 'errorBoxEdit');
            }
            break;

        default:
            break;
    }
    $flushres = Shop::Container()->getCache()->flushTags([CACHING_GROUP_OBJECT, CACHING_GROUP_BOX, 'boxes']);
    Shop::Container()->getDB()->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
}
$oBoxen_arr      = $boxService->buildList($pageID, false, true);
$oVorlagen_arr   = $boxAdmin->getTemplates($pageID);
$oBoxenContainer = Template::getInstance()->getBoxLayoutXML();
$filterMapping   = [];
if ($pageID === PAGE_ARTIKELLISTE) { //map category name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kKategorie AS id, cName AS name FROM tkategorie',
        ReturnType::ARRAY_OF_OBJECTS
    );
} elseif ($pageID === PAGE_ARTIKEL) { //map article name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kArtikel AS id, cName AS name FROM tartikel',
        ReturnType::ARRAY_OF_OBJECTS
    );
} elseif ($pageID === PAGE_HERSTELLER) { //map manufacturer name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kHersteller AS id, cName AS name FROM thersteller',
        ReturnType::ARRAY_OF_OBJECTS
    );
} elseif ($pageID === PAGE_EIGENE) { //map page name
    $filterMapping = Shop::Container()->getDB()->query(
        'SELECT kLink AS id, cName AS name FROM tlink',
        ReturnType::ARRAY_OF_OBJECTS
    );
}

$filterMapping = \Functional\reindex($filterMapping, function ($e) {
    return $e->id;
});
$filterMapping = \Functional\map($filterMapping, function ($e) {
    return $e->name;
});
$smarty->assign('filterMapping', $filterMapping)
       ->assign('validPageTypes', $boxAdmin->getMappedValidPageTypes())
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
