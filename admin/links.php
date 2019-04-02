<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\PlausiCMS;
use JTL\Shop;
use JTL\Sprache;
use JTL\DB\ReturnType;
use JTL\Link\Link;
use JTL\Link\LinkInterface;
use JTL\Link\Admin\LinkAdmin;
use JTL\Alert\Alert;

require_once __DIR__ . '/includes/admininclude.php';

$oAccount->permission('CONTENT_PAGE_VIEW', true, true);
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'toolsajax_inc.php';
require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'links_inc.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
$step               = 'uebersicht';
$link               = null;
$cUploadVerzeichnis = PFAD_ROOT . PFAD_BILDER . PFAD_LINKBILDER;
$clearCache         = false;
$continue           = true;
$db                 = Shop::Container()->getDB();
$cache              = Shop::Container()->getCache();
$linkAdmin          = new LinkAdmin($db, $cache);
$alertHelper        = Shop::Container()->getAlertService();
if (isset($_POST['addlink']) && (int)$_POST['addlink'] > 0) {
    $step = 'neuer Link';
    $link = new Link($db);
    $link->setLinkGroupID((int)$_POST['addlink']);
    $link->setLinkGroups([(int)$_POST['addlink']]);
}

if (isset($_POST['removefromlinkgroup'], $_POST['kLinkgruppe'])
    && (int)$_POST['removefromlinkgroup'] > 0
    && Form::validateToken()
) {
    $res = $linkAdmin->removeLinkFromLinkGroup((int)$_POST['removefromlinkgroup'], (int)$_POST['kLinkgruppe']);
    if ($res > 0) {
        $alertHelper->addAlert(
            Alert::TYPE_SUCCESS,
            __('successLinkFromLinkGroupDelete'),
            'successLinkFromLinkGroupDelete'
        );
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkFromLinkGroupDelete'), 'errorLinkFromLinkGroupDelete');
    }
    unset($_POST['kLinkgruppe']);
    $step       = 'uebersicht';
    $clearCache = true;
}

if (isset($_POST['dellink']) && (int)$_POST['dellink'] > 0 && Form::validateToken()) {
    $res = $linkAdmin->deleteLink((int)$_POST['dellink']);
    if ($res > 0) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successLinkDelete'), 'successLinkDelete');
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkDelete'), 'errorLinkDelete');
    }
    $clearCache = true;
    $step       = 'uebersicht';
    $_POST      = [];
}

if (isset($_POST['loesch_linkgruppe']) && (int)$_POST['loesch_linkgruppe'] === 1 && Form::validateToken()) {
    if (isset($_POST['loeschConfirmJaSubmit'])) {
        $step = 'loesch_linkgruppe';
    } else {
        $step  = 'uebersicht';
        $_POST = [];
    }
}

if (((isset($_POST['dellinkgruppe']) && (int)$_POST['dellinkgruppe'] > 0)
        || $step === 'loesch_linkgruppe')
    && Form::validateToken()
) {
    $step        = 'uebersicht';
    $linkGroupID = 0;
    if (isset($_POST['dellinkgruppe'])) {
        $linkGroupID = (int)$_POST['dellinkgruppe'];
    }
    if ((int)$_POST['kLinkgruppe'] > 0) {
        $linkGroupID = (int)$_POST['kLinkgruppe'];
    }
    if ($linkAdmin->deleteLinkGroup($linkGroupID) > 0) {
        $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successLinkGroupDelete'), 'successLinkGroupDelete');
        $clearCache = true;
        $step       = 'uebersicht';
        $_POST      = [];
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkGroupDelete'), 'errorLinkGroupDelete');
    }
}

if (isset($_POST['delconfirmlinkgruppe']) && (int)$_POST['delconfirmlinkgruppe'] > 0 && Form::validateToken()) {
    $step = 'linkgruppe_loeschen_confirm';

    $smarty->assign('oLinkgruppe', holeLinkgruppe((int)$_POST['delconfirmlinkgruppe']))
           ->assign('affectedLinkNames', $linkAdmin->getPreDeletionLinks((int)$_POST['delconfirmlinkgruppe'], true));
}

if (isset($_POST['neu_link']) && (int)$_POST['neu_link'] === 1 && Form::validateToken()) {
    $sprachen    = Sprache::getAllLanguages();
    $hasHTML_arr = [];

    foreach ($sprachen as $sprache) {
        $hasHTML_arr[] = 'cContent_' . $sprache->cISO;
    }
    // Plausi
    $oPlausiCMS = new PlausiCMS();
    $oPlausiCMS->setPostVar($_POST, $hasHTML_arr, true);
    $oPlausiCMS->doPlausi('lnk');

    if (count($oPlausiCMS->getPlausiVar()) === 0) {
        $link = $linkAdmin->createOrUpdateLink($_POST);
        if ((int)$_POST['kLink'] === 0) {
            $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successLinkCreate'), 'successLinkCreate');
        } else {
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('successLinkEdit'), $link->getDisplayName()),
                'successLinkEdit'
            );
        }
        $clearCache = true;
        $kLink      = $link->getID();
        $step       = 'uebersicht';
        $continue   = (isset($_POST['continue']) && (int)$_POST['continue'] === 1);
        if ($continue) {
            $step           = 'neuer Link';
            $_POST['kLink'] = $kLink;
        }
        // Bilder hochladen
        if (!is_dir($cUploadVerzeichnis . $kLink)) {
            mkdir($cUploadVerzeichnis . $kLink);
        }
        if (is_array($_FILES['Bilder']['name']) && count($_FILES['Bilder']['name']) > 0) {
            $nLetztesBild = gibLetzteBildNummer($kLink);
            $nZaehler     = 0;
            if ($nLetztesBild > 0) {
                $nZaehler = $nLetztesBild;
            }
            $imageCount = (count($_FILES['Bilder']['name']) + $nZaehler);
            for ($i = $nZaehler; $i < $imageCount; ++$i) {
                if (!empty($_FILES['Bilder']['size'][$i - $nZaehler])
                    && $_FILES['Bilder']['error'][$i - $nZaehler] === UPLOAD_ERR_OK
                ) {
                    $type         = $_FILES['Bilder']['type'][$i - $nZaehler];
                    $cUploadDatei = $cUploadVerzeichnis . $kLink . '/Bild' . ($i + 1) . '.' .
                        mb_substr(
                            $type,
                            mb_strpos($type, '/') + 1,
                            mb_strlen($type) - mb_strpos($type, '/') + 1
                        );
                    move_uploaded_file($_FILES['Bilder']['tmp_name'][$i - $nZaehler], $cUploadDatei);
                }
            }
        }
    } else {
        $step = 'neuer Link';
        $link = new Link($db);
        $link->setLinkGroupID((int)$_POST['kLinkgruppe']);
        $link->setLinkGroups([(int)$_POST['kLinkgruppe']]);
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
        $smarty->assign('xPlausiVar_arr', $oPlausiCMS->getPlausiVar())
               ->assign('xPostVar_arr', $oPlausiCMS->getPostVar());
    }
} elseif (((isset($_POST['neuelinkgruppe']) && (int)$_POST['neuelinkgruppe'] === 1)
        || (isset($_POST['kLinkgruppe']) && (int)$_POST['kLinkgruppe'] > 0))
    && Form::validateToken()
) {
    $step = 'neue Linkgruppe';
    if (isset($_POST['kLinkgruppe']) && (int)$_POST['kLinkgruppe'] > 0) {
        $linkgruppe = $db->select('tlinkgruppe', 'kLinkgruppe', (int)$_POST['kLinkgruppe']);
        $smarty->assign('Linkgruppe', $linkgruppe)
               ->assign('Linkgruppenname', getLinkgruppeNames($linkgruppe->kLinkgruppe));
    }
}
if ($continue
    && ((isset($_POST['kLink']) && (int)$_POST['kLink'] > 0)
        || (isset($_GET['kLink'], $_GET['delpic']) && (int)$_GET['kLink']))
    && Form::validateToken()
) {
    $step = 'neuer Link';
    $link = (new Link($db))->load(Request::verifyGPCDataInt('kLink'));
    $smarty->assign('Link', $link);
    // Bild loeschen?
    if (Request::verifyGPCDataInt('delpic') === 1) {
        @unlink($cUploadVerzeichnis . $link->getID() . '/' . Request::verifyGPDataString('cName'));
    }
    $cDatei_arr = [];
    if (is_dir($cUploadVerzeichnis . $link->getID())) {
        $DirHandle = opendir($cUploadVerzeichnis . $link->getID());
        $shopURL   = Shop::getURL() . '/';
        while (($Datei = readdir($DirHandle)) !== false) {
            if ($Datei !== '.' && $Datei !== '..') {
                $nImageGroesse_arr = calcRatio(
                    PFAD_ROOT . '/' . PFAD_BILDER . PFAD_LINKBILDER . $link->getID() . '/' . $Datei,
                    160,
                    120
                );
                $oDatei            = new stdClass();
                $oDatei->cName     = mb_substr($Datei, 0, mb_strpos($Datei, '.'));
                $oDatei->cNameFull = $Datei;
                $oDatei->cURL      = '<img class="link_image" src="' .
                    $shopURL . PFAD_BILDER . PFAD_LINKBILDER . $link->getID() . '/' . $Datei . '" />';
                $oDatei->nBild     = (int)mb_substr(
                    str_replace('Bild', '', $Datei),
                    0,
                    mb_strpos(str_replace('Bild', '', $Datei), '.')
                );
                $cDatei_arr[]      = $oDatei;
            }
        }
        usort($cDatei_arr, 'cmp_obj');
        $smarty->assign('cDatei_arr', $cDatei_arr);
    }
}

if (isset($_POST['neu_linkgruppe']) && (int)$_POST['neu_linkgruppe'] === 1 && Form::validateToken()) {
    // Plausi
    $oPlausiCMS = new PlausiCMS();
    $oPlausiCMS->setPostVar($_POST);
    $oPlausiCMS->doPlausi('grp');

    if (count($oPlausiCMS->getPlausiVar()) === 0) {
        $kLinkgruppe             = 0;
        $linkGroupTemplateExists = Shop::Container()->getDB()->select(
            'tlinkgruppe',
            'cTemplatename',
            $_POST['cTemplatename']
        );
        if ($linkGroupTemplateExists !== null && $_POST['kLinkgruppe'] !== $linkGroupTemplateExists->kLinkgruppe) {
            $step = 'neue Linkgruppe';
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorTemplateNameDuplicate'), 'errorTemplateNameDuplicate');
            $smarty->assign('xPlausiVar_arr', $oPlausiCMS->getPlausiVar())
                ->assign('xPostVar_arr', $oPlausiCMS->getPostVar());
        } else {
            if ((int)$_POST['kLinkgruppe'] === 0) {
                $linkAdmin->createOrUpdateLinkGroup(0, $_POST);
                $alertHelper->addAlert(Alert::TYPE_SUCCESS, __('successLinkGroupCreate'), 'successLinkGroupCreate');
            } else {
                $linkgruppe = $linkAdmin->createOrUpdateLinkGroup((int)$_POST['kLinkgruppe'], $_POST);
                $alertHelper->addAlert(
                    Alert::TYPE_SUCCESS,
                    sprintf(__('successLinkGroupEdit'), $linkgruppe->cName),
                    'successLinkGroupEdit'
                );
            }
            $step = 'uebersicht';
        }

        $clearCache = true;
    } else {
        $step = 'neue Linkgruppe';
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorFillRequired'), 'errorFillRequired');
        $smarty->assign('xPlausiVar_arr', $oPlausiCMS->getPlausiVar())
               ->assign('xPostVar_arr', $oPlausiCMS->getPostVar());
    }
}
// Verschiebt einen Link in eine andere Linkgruppe
if (isset($_POST['aender_linkgruppe']) && (int)$_POST['aender_linkgruppe'] === 1 && Form::validateToken()) {
    if ((int)$_POST['kLink'] > 0 && (int)$_POST['kLinkgruppe'] > 0 && (int)$_POST['kLinkgruppeAlt'] >= -1) {
        $res = $linkAdmin->updateLinkGroup(
            (int)$_POST['kLink'],
            (int)$_POST['kLinkgruppeAlt'],
            (int)$_POST['kLinkgruppe']
        );
        if ($res === LinkAdmin::ERROR_LINK_ALREADY_EXISTS) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkMoveDuplicate'), 'errorLinkMoveDuplicate');
        } elseif ($res === LinkAdmin::ERROR_LINK_NOT_FOUND) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkKeyNotFound'), 'errorLinkKeyNotFound');
        } elseif ($res === LinkAdmin::ERROR_LINK_GROUP_NOT_FOUND) {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkGroupKeyNotFound'), 'errorLinkGroupKeyNotFound');
        } elseif ($res instanceof LinkInterface) {
            $alertHelper->addAlert(
                Alert::TYPE_SUCCESS,
                sprintf(__('successLinkMove'), $link->getDisplayName()),
                'successLinkMove'
            );
            $step       = 'uebersicht';
            $clearCache = true;
        } else {
            $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorUnknownLong'), 'errorUnknownLong');
        }
    }
    $step = 'uebersicht';
}
if (isset($_POST['kopiere_in_linkgruppe'])
    && (int)$_POST['kopiere_in_linkgruppe'] === 1
    && (int)$_POST['kLink'] > 0
    && (int)$_POST['kLinkgruppe'] > 0
    && Form::validateToken()
) {
    $res = $linkAdmin->copyLinkToLinkGroup((int)$_POST['kLink'], (int)$_POST['kLinkgruppe']);
    if ($res === LinkAdmin::ERROR_LINK_ALREADY_EXISTS) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkCopyDuplicate'), 'errorLinkCopyDuplicate');
    } elseif ($res === LinkAdmin::ERROR_LINK_NOT_FOUND) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkKeyNotFound'), 'errorLinkKeyNotFound');
    } elseif ($res === LinkAdmin::ERROR_LINK_GROUP_NOT_FOUND) {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkGroupKeyNotFound'), 'errorLinkGroupKeyNotFound');
    } elseif ($res instanceof LinkInterface) {
        $alertHelper->addAlert(
            Alert::TYPE_SUCCESS,
            sprintf(__('successLinkCopy'), $link->getDisplayName()),
            'successLinkCopy'
        );
        $step       = 'uebersicht';
        $clearCache = true;
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorUnknownLong'), 'errorUnknownLong');
    }
}
// Ordnet einen Link neu an
if (isset($_POST['aender_linkvater']) && (int)$_POST['aender_linkvater'] === 1 && Form::validateToken()) {
    if ((int)$_POST['kLink'] > 0
        && (int)$_POST['kVaterLink'] >= 0
        && (int)$_POST['kLinkgruppe'] > 0
        && ($oLink = $linkAdmin->updateParentID((int)$_POST['kLink'], (int)$_POST['kVaterLink'])) !== false
    ) {
        $alertHelper->addAlert(
            Alert::TYPE_SUCCESS,
            sprintf(__('successLinkMove'), $oLink->cName),
            'successLinkMove'
        );
        $step       = 'uebersicht';
        $clearCache = true;
    } else {
        $alertHelper->addAlert(Alert::TYPE_ERROR, __('errorLinkMove'), 'errorLinkMove');
    }
}
if ($clearCache === true) {
    $linkAdmin->clearCache();
}
if ($step === 'uebersicht') {
    $smarty->assign('kPlugin', Request::verifyGPCDataInt('kPlugin'))
           ->assign('linkGroupCountByLinkID', $linkAdmin->getLinkGroupCountForLinkIDs())
           ->assign('linkgruppen', $linkAdmin->getLinkGroups());
}
if ($step === 'neuer Link') {
    $kundengruppen = $db->query('SELECT * FROM tkundengruppe ORDER BY cName', ReturnType::ARRAY_OF_OBJECTS);
    $smarty->assign('Link', $link)
           ->assign('oSpezialseite_arr', holeSpezialseiten())
           ->assign('sprachen', Sprache::getAllLanguages())
           ->assign('kundengruppen', $kundengruppen)
           ->assign('gesetzteKundengruppen', getGesetzteKundengruppen($link));
}
$smarty->assign('step', $step)
       ->assign('sprachen', Sprache::getAllLanguages())
       ->assign('linkAdmin', $linkAdmin)
       ->display('links.tpl');
